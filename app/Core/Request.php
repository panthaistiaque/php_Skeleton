<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP request wrapper. Raw user input is NEVER trusted here; output
 * escaping happens in the view layer via the e()/h() helpers.
 */
final class Request
{
    private array $query;
    private array $body;
    private array $files;
    private array $attributes = [];
    private string $path = '/';
    private array $json = [];

    public function __construct()
    {
        $this->query = $_GET;
        $this->files = $_FILES;
        $this->body = $_POST;

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = (string)file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $this->json = $decoded;
            }
        }
    }

    public function setPath(string $path): void
    {
        $this->path = $path === '' ? '/' : '/' . ltrim($path, '/');
    }

    public function path(): string
    {
        return $this->path;
    }

    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Support method spoofing for forms
        if ($method === 'POST' && ($override = strtoupper((string)($this->body['_method'] ?? ''))) !== '') {
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }

        return $method;
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isAjax(): bool
    {
        return strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $this->json[$key] ?? $default;
    }

    public function body(string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->body : ($this->body[$key] ?? $default);
    }

    public function query(string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->query : ($this->query[$key] ?? $default);
    }

    public function json(string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->json : ($this->json[$key] ?? $default);
    }

    public function all(): array
    {
        return array_replace($this->query, $this->body, $this->json);
    }

    public function only(array $keys): array
    {
        $data = $this->all();

        return array_intersect_key($data, array_flip($keys));
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->body) || array_key_exists($key, $this->query) || array_key_exists($key, $this->json);
    }

    /**
     * @return array{name:string,type:string,tmp_name:string,error:int,size:int}|null
     */
    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;
        if (!is_array($file) || (isset($file['error']) && (int)$file['error'] !== UPLOAD_ERR_OK)) {
            return null;
        }

        return $file;
    }

    public function hasFile(string $key): bool
    {
        return $this->file($key) !== null;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function ip(): string
    {
        return (string)filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: '0.0.0.0';
    }

    public function userAgent(): string
    {
        return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
    }

    public function fullUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

        return "{$scheme}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    }

    public function referer(?string $default = ''): string
    {
        return (string)($_SERVER['HTTP_REFERER'] ?? $default);
    }
}