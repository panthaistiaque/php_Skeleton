<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\HttpException;
use App\Core\Request;

/**
 * Blocks state-changing requests without a valid CSRF token.
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            if (!Csrf::validate()) {
                throw new HttpException('The page has expired. Please try again (CSRF token mismatch).', 419);
            }
        }

        return $next();
    }
}