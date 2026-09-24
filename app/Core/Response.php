<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Tiny response builder. Keeps controllers framework-flavoured;
 * view output is produced by the View class.
 */
final class Response
{
    public static function make(string $content, int $statusCode = 200, array $headers = []): string
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }

        return $content;
    }

    public static function json(mixed $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function noContent(): string
    {
        http_response_code(204);

        return '';
    }

    public static function redirect(string $url, int $statusCode = 302): void
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $url = \url($url);
        }
        throw new RedirectResponse($url, $statusCode);
    }

    public static function back(?string $fallback = '/'): void
    {
        $url = $_SERVER['HTTP_REFERER'] ?? $fallback;
        self::redirect((string)$url);
    }

    public static function download(string $path, ?string $filename = null): never
    {
        if (!is_file($path)) {
            throw new HttpException('File not found.', 404);
        }
        $filename ??= basename($path);
        header('Content-Type: ' . (mime_content_type($path) ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . (string)filesize($path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($path);
        exit;
    }
}