<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;

/**
 * Bearer token authentication for the /api/v1 endpoints.
 * Tokens are stored hashed (sha256) - the plain value is shown only once.
 */
final class ApiAuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        $header = $request->input('token') ?: ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        $token = null;

        if ($request->input('token')) {
            $token = $request->input('token');
        } elseif (is_string($header) && preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            $token = trim($m[1]);
        }

        if ($token === null || $token === '') {
            throw new HttpException('Unauthorized. Provide a Bearer token.', 401);
        }

        $hash = hash('sha256', $token);
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM `api_tokens` WHERE `token_hash` = :hash AND `revoked_at` IS NULL LIMIT 1'
        );
        $stmt->execute([':hash' => $hash]);
        $apiToken = $stmt->fetch();

        if ($apiToken === false) {
            throw new HttpException('Invalid API token.', 401);
        }

        if ($apiToken['expires_at'] !== null && strtotime($apiToken['expires_at']) < time()) {
            throw new HttpException('API token has expired.', 401);
        }

        // Bind user
        $stmt = Database::pdo()->prepare('SELECT * FROM `users` WHERE `id` = :id LIMIT 1');
        $stmt->execute([':id' => (int)$apiToken['user_id']]);
        $user = $stmt->fetch();
        if ($user === false || ($user['status'] ?? '') !== 'active') {
            throw new HttpException('Account is not active.', 401);
        }

        $request->setAttribute('api_token', $apiToken);
        $request->setAttribute('api_user', $user);

        // Track usage
        $stmt = Database::pdo()->prepare('UPDATE `api_tokens` SET `last_used_at` = :now WHERE `id` = :id');
        $stmt->execute([':now' => date('Y-m-d H:i:s'), ':id' => (int)$apiToken['id']]);

        return $next();
    }
}