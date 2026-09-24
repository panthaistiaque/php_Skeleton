<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;
use App\Core\Mailer;

/**
 * Outbound mail. When SMTP is not configured/enabled, emails are written to
 * storage/mails/ so the flow stays testable during development.
 */
final class MailService
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function smtpConfig(): array
    {
        return [
            'enabled'    => (bool)setting('smtp.enabled', (bool)config('mail.enabled', false)),
            'host'       => (string)setting('smtp.host', (string)config('mail.host', '')),
            'port'       => (int)setting('smtp.port', (int)config('mail.port', 587)),
            'username'   => (string)setting('smtp.username', (string)config('mail.username', '')),
            'password'   => (string)setting('smtp.password', (string)config('mail.password', '')),
            'encryption' => (string)setting('smtp.encryption', (string)config('mail.encryption', 'tls')),
            'from_email' => (string)setting('smtp.from_email', (string)config('mail.from_email', 'noreply@local.test')),
            'from_name'  => (string)setting('smtp.from_name', (string)config('mail.from_name', 'Skeleton App')),
        ];
    }

    public function send(array|string $to, string $subject, string $html, string $plain = ''): bool
    {
        $smtp = $this->smtpConfig();

        if (!$smtp['enabled'] || $smtp['host'] === '') {
            $this->writeToLog($to, $subject, $html);
            Logger::info('Mail logged instead of sent (SMTP disabled)', ['to' => is_array($to) ? $to : $to]);

            return true;
        }

        $mailer = new Mailer();
        $mailer->send($smtp, ['to' => is_array($to) ? $to : [$to]], $subject, $html, $plain);

        return true;
    }

    public function test(array|string $to): bool
    {
        return $this->send($to, 'Skeleton App - SMTP test', '<h2>SMTP is configured correctly.</h2><p>This is a test message sent at ' . date('Y-m-d H:i:s') . '.</p>', 'SMTP is configured correctly.');
    }

    // -- Template helpers -----------------------------------------------------

    public function sendVerification(string $email, string $name, string $link): bool
    {
        return $this->send($email, 'Verify your email address', $this->template(
            'Verify your email address',
            "<p>Hi <strong>" . e($name) . "</strong>,</p>
             <p>Thanks for registering. Please confirm your email address by clicking the button below.</p>",
            $link,
            'Verify Email'
        ), "Hi {$name},\n\nConfirm your email address here: {$link}");
    }

    public function sendPasswordReset(string $email, string $name, string $link): bool
    {
        return $this->send($email, 'Reset your password', $this->template(
            'Reset your password',
            "<p>Hi <strong>" . e($name) . "</strong>,</p>
             <p>We received a request to reset your password. Click below to choose a new one. This link expires in 1 hour.</p>",
            $link,
            'Reset Password'
        ), "Hi {$name},\n\nReset your password here: {$link}");
    }

    public function sendWelcome(string $email, string $name, string $password): bool
    {
        return $this->send($email, 'Your account has been created', $this->template(
            'Your account has been created',
            "<p>Hi <strong>" . e($name) . "</strong>,</p>
             <p>An administrator created an account for you on <strong>" . e(setting('app.name', 'Skeleton App')) . "</strong>.</p>
             <p>Your temporary password is: <code>" . e($password) . "</code></p>
             <p>Please sign in and change it immediately.</p>",
            url('/login'),
            'Sign In'
        ), "Hi {$name},\n\nYour account has been created. Temporary password: {$password}\nSign in: " . url('/login'));
    }

    private function template(string $title, string $bodyHtml, string $buttonUrl, string $buttonLabel): string
    {
        $appName = e(setting('app.name', 'Skeleton App'));
        $bodySnippet = $bodyHtml . ($buttonUrl !== '' ? '
            <p style="text-align:center;margin:28px 0;">
                <a href="' . e($buttonUrl) . '" style="background:#0d6efd;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;display:inline-block;">' . e($buttonLabel) . '</a>
            </p>' : '');

        return '<!DOCTYPE html>
        <html><body style="margin:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">
            <div style="max-width:600px;margin:0 auto;padding:24px;">
                <div style="background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e3e6ec;">
                    <div style="background:#0d6efd;padding:18px 24px;color:#fff;font-size:18px;font-weight:bold;">' . $appName . '</div>
                    <div style="padding:28px 24px;color:#333;font-size:14px;line-height:1.6;">
                        <h2 style="margin-top:0;font-size:18px;">' . e($title) . '</h2>' . $bodySnippet . '
                    </div>
                    <div style="padding:14px 24px;border-top:1px solid #e3e6ec;font-size:12px;color:#8a93a3;">&copy; ' . date('Y') . ' ' . $appName . '. All rights reserved.</div>
                </div>
            </div>
        </body></html>';
    }

    private function writeToLog(array|string $to, string $subject, string $html): void
    {
        $dir = STORAGE_PATH . '/mails';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $recipients = is_array($to) ? json_encode($to, JSON_UNESCAPED_UNICODE) : $to;
        $content = "To: {$recipients}\nSubject: {$subject}\n\n{$html}";
        @file_put_contents($dir . '/email-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.html', $content, LOCK_EX);
    }
}