<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Redirects already-authenticated users away from guest pages.
 */
final class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        if (Session::isAuthenticated()) {
            Response::redirect('/home');
        }

        return $next();
    }
}