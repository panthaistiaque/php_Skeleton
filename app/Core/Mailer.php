<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal SMTP client (no external dependencies).
 * Supports EHLO, STARTTLS/TLS, AUTH LOGIN and a single transaction.
 */
final class Mailer
{
    private $socket = null;

    /**
     * @param array{
     *   host:string, port:int, username:string, password:string,
     *   encryption:string, from_email:string, from_name:string
     * } $smtp
     */
    public function send(array $smtp, array $to, string $subject, string $html, string $plain = ''): bool
    {
        $host = (string)($smtp['host'] ?? 'localhost');
        $port = (int)($smtp['port'] ?? 587);
        $username = (string)($smtp['username'] ?? '');
        $password = (string)($smtp['password'] ?? '');
        $encryption = strtolower((string)($smtp['encryption'] ?? 'tls'));
        $fromEmail = (string)($smtp['from_email'] ?? 'noreply@local.test');
        $fromName = (string)($smtp['from_name'] ?? 'Skeleton App');

        $timeout = 30;
        $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $this->socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
        if (!$this->socket) {
            throw new \RuntimeException("SMTP connection to {$host}:{$port} failed: {$errstr} ({$errno})");
        }
        stream_set_timeout($this->socket, $timeout);

        try {
            $this->expect(220);

            $this->command('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
            $this->consume();

            if ($encryption === 'tls' && $username !== '') {
                $this->command('STARTTLS');
                $this->expect(220);
                $result = @stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if ($result !== true) {
                    throw new \RuntimeException('STARTTLS handshake failed.');
                }
                $this->command('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
                $this->consume();
            }

            if ($username !== '') {
                $this->command('AUTH LOGIN');
                $this->expect(334);
                $this->command(base64_encode($username));
                $this->expect(334);
                $this->command(base64_encode($password));
                $this->expect(235);
            }

            $this->command('MAIL FROM:<' . $fromEmail . '>');
            $this->expect(250);

            foreach (($to['to'] ?? [$to]) as $recipient) {
                $address = is_array($recipient) ? ($recipient['email'] ?? '') : $recipient;
                $this->command('RCPT TO:<' . $address . '>');
                $this->expect(250);
            }
            if (isset($to['cc'])) {
                foreach ($to['cc'] as $recipient) {
                    $address = is_array($recipient) ? ($recipient['email'] ?? '') : $recipient;
                    $this->command('RCPT TO:<' . $address . '>');
                    $this->expect(250);
                }
            }

            $this->command('DATA');
            $this->expect(354);
            $this->command($this->buildMime($smtp, $to, $subject, $html, $plain));
            $this->expect(250);

            $this->command('QUIT');
        } finally {
            $this->close();
        }

        return true;
    }

    private function buildMime(array $smtp, array $to, string $subject, string $html, string $plain): string
    {
        $fromEmail = (string)($smtp['from_email'] ?? 'noreply@local.test');
        $fromName = (string)($smtp['from_name'] ?? 'Skeleton App');

        $boundary = 'b_' . bin2hex(random_bytes(12));
        $toAddresses = [];
        foreach (($to['to'] ?? [$to]) as $recipient) {
            $toAddresses[] = is_array($recipient) ? ($recipient['email'] ?? '') : $recipient;
        }

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . (($host = parse_url($_SERVER['HTTP_HOST'] ?? '', PHP_URL_HOST)) ?: ($_SERVER['SERVER_NAME'] ?? 'skeleton.local')) . '>';
        $headers[] = 'From: =?UTF-8?B?' . base64_encode($fromName) . "?= <{$fromEmail}>";
        $headers[] = 'To: ' . implode(', ', $toAddresses);
        $headers[] = 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

        $body = '';

        if ($plain !== '') {
            $body .= '--' . $boundary . "\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($plain), 76, "\r\n") . "\r\n";
        }

        $body .= '--' . $boundary . "\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($html), 76, "\r\n") . "\r\n";
        $body .= '--' . $boundary . "--\r\n";

        return implode("\r\n", $headers) . "\r\n\r\n" . $body . '.';
    }

    private function command(string $data): void
    {
        fwrite($this->socket, $data . "\r\n");
    }

    private function read(): string
    {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            // Multi-line response: 250-xxxx continues until "250 "
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }

        return $response;
    }

    private function consume(): void
    {
        $this->read();
    }

    private function expect(int $code): string
    {
        $response = trim($this->read());
        if ($response === '') {
            throw new \RuntimeException('SMTP: empty response (server closed connection).');
        }
        $actual = (int)substr($response, 0, 3);
        if ($actual !== $code) {
            throw new \RuntimeException("SMTP: expected {$code}, got {$actual} -> {$response}");
        }

        return $response;
    }

    private function close(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }
}