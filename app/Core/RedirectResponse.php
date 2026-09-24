<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Thrown when a controller wants to redirect; caught by the dispatcher
 * so middleware/controllers can remain type-safe.
 */
class RedirectResponse extends \RuntimeException
{
    public function __construct(private readonly string $url, private readonly int $statusCode = 302)
    {
        parent::__construct($url, $statusCode);
    }

    public function url(): string
    {
        return $this->url;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}