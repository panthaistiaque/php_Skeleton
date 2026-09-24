<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\HttpException;
use App\Models\ApiToken;
use App\Models\LoginHistory;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Services\AuditService;

/**
 * Token-based auth for the API foundation.
 * POST /api/v1/auth/login  {email, password}  ->  {token, ...}
 */
final class AuthController extends Controller
{
    public function login(): string
    {
        $email = (string)$this->request->json('email', '');
        $password = (string)$this->request->json('password', '');

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || $password === '') {
            return $this->json(['error' => 'email and password are required'], 422);
        }

        $user = User::findByEmail($email);
        $ip = $this->request->ip();

        if ($user === null || !password_verify($password, (string)$user['password'])) {
            LoginHistory::createEntry([
                'user_id'    => $user['id'] ?? null,
                'email'      => $email,
                'ip_address' => $ip,
                'user_agent' => $this->request->userAgent(),
                'device_type'=> 'api',
                'status'     => 'failed',
                'reason'     => $user === null ? 'unknown_email' : 'invalid_password',
            ]);

            throw new HttpException('Invalid credentials.', 401);
        }

        if ($user['status'] !== 'active') {
            throw new HttpException('Account is not active.', 403);
        }

        $attempts = (int)$user['failed_attempts'];
        $max = (int)setting('security.max_failed_attempts', 5);
        if ($attempts >= $max) {
            throw new HttpException('Account temporarily locked due to failed attempts.', 423);
        }

        [$token, $tokenId] = ApiToken::createFor((int)$user['id'], 'api-login@' . date('YmdHis'));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        User::updateById((int)$user['id'], [
            'failed_attempts' => 0,
            'last_login_at'   => date('Y-m-d H:i:s'),
            'last_login_ip'   => $ip,
        ]);

        LoginHistory::createEntry([
            'user_id'    => (int)$user['id'],
            'email'      => $email,
            'ip_address' => $ip,
            'user_agent' => $this->request->userAgent(),
            'device_type'=> 'api',
            'status'     => 'success',
        ]);

        AuditService::instance()->log([
            'user_id'     => (int)$user['id'],
            'action'      => 'api_login',
            'module'      => 'api',
            'description' => 'API token issued',
            'ip_address'  => $ip,
        ]);

        return $this->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
            'user'       => [
                'id'    => (int)$user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
            ],
        ]);
    }
}