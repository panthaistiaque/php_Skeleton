<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;
use App\Services\PermissionService;

/**
 * Restricts a route to users holding a given permission slug.
 */
final class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $permission) {}

    public function handle(Request $request, callable $next): mixed
    {
        if ($this->permission === '' || !PermissionService::instance()->has($this->permission)) {
            throw new HttpException('You do not have permission to view this page.', 403);
        }

        return $next();
    }
}