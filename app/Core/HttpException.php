<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Represents an HTTP error that should be rendered to the user
 * (403/404/405/419/500).
 */
class HttpException extends \RuntimeException
{
    public function __construct(string $message, public int $statusCode = 500, public array $context = [])
    {
        parent::__construct($message, $statusCode);
    }
}