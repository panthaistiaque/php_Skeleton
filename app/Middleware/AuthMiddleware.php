<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

/**
 * Requires a valid, active, authenticated session.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        $userId = Session::getAuth();

        if ($userId === null) {
            Session::set('_intended', $request->fullUrl());
            Session::flash('error', 'Please sign in to continue.');
            Response::redirect('/login');
        }

        $user = User::find($userId);
        if ($user === null || ($user['status'] ?? '') !== 'active') {
            Session::flash('error', 'Your account is not active. Contact an administrator.');
            Session::logout();
            Response::redirect('/login');
        }

        // Keep the user row cached for the request
        $request->setAttribute('user', $user);

        return $next();
    }
}