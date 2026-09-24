<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Session;
use App\Models\EmailVerification;
use App\Models\LoginHistory;
use App\Models\PasswordReset;
use App\Models\SecurityEvent;
use App\Models\User;

/**
 * Authentication domain logic: register, verify, login (with brute-force
 * protection), remember-me, forgot/reset password, change password, logout.
 */
final class AuthService
{
    private const REMEMBER_COOKIE = 'skel_remember';

    public static function instance(): self
    {
        return new self();
    }

    // -- Registration ---------------------------------------------------------

    /**
     * Creates a user in `pending` status and dispatches the verification email.
     *
     * @return array{success:bool,message:string,user:?array}
     */
    public function register(array $data): array
    {
        if (!(bool)setting('security.register_enabled', true)) {
            return ['success' => false, 'message' => 'Registration is disabled by the administrator.', 'user' => null];
        }

        $verifiedImmediately = !(bool)setting('security.require_verification', true);

        $userId = (int)User::insertGetId([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $this->hash($data['password']),
            'status'   => $verifiedImmediately ? 'active' : 'pending',
            'email_verified_at' => $verifiedImmediately ? date('Y-m-d H:i:s') : null,
            'password_changed_at' => date('Y-m-d H:i:s'),
        ]);

        $user = User::find($userId);
        if ($user === null) {
            return ['success' => false, 'message' => 'Could not create the account.', 'user' => null];
        }

        if (!$verifiedImmediately) {
            $this->sendVerificationEmail((int)$user['id'], $user['name'], $user['email']);
        } else {
            $this->assignDefaultRole($userId);
        }

        AuditService::instance()->log([
            'user_id'     => $userId,
            'action'      => 'user_registered',
            'module'      => 'auth',
            'description' => 'New account registered: ' . $user['email'],
            'ip_address'  => request_ip(),
            'user_agent'  => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ]);

        return ['success' => true, 'message' => 'Account created.', 'user' => $user];
    }

    private function assignDefaultRole(int $userId): void
    {
        // Attach the lowest privilege role (slug 'user') when present.
        $stmt = \App\Core\Database::pdo()->prepare(
            'INSERT IGNORE INTO `user_role` (`user_id`, `role_id`)
             SELECT :uid, `id` FROM `roles` WHERE `slug` = "user" LIMIT 1'
        );
        $stmt->execute([':uid' => $userId]);
    }

    public function sendVerificationEmail(int $userId, string $name, string $email): void
    {
        $token = random_token(48);
        EmailVerification::insertGetId([
            'user_id'    => $userId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => gmdate('Y-m-d H:i:s', time() + 172800),
        ]);

        $link = url('/verify-email/' . $token);
        MailService::instance()->sendVerification($email, $name, $link);
    }

    /**
     * @return array{success:bool,message:string}
     */
    public function verifyEmail(string $token): array
    {
        $hash = hash('sha256', $token);
        $pdo = \App\Core\Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT v.*, u.name, u.email FROM `email_verifications` v
             JOIN `users` u ON u.id = v.user_id
             WHERE v.token_hash = :hash AND v.used_at IS NULL AND v.expires_at > :now LIMIT 1'
        );
        $stmt->execute([':hash' => $hash, ':now' => gmdate('Y-m-d H:i:s')]);
        $row = $stmt->fetch();

        if ($row === false) {
            return ['success' => false, 'message' => 'This verification link is invalid or expired.'];
        }

        \App\Core\Database::transaction(function () use ($row): void {
            EmailVerification::updateById((int)$row['id'], ['used_at' => date('Y-m-d H:i:s')]);
            User::updateById((int)$row['user_id'], [
                'email_verified_at' => date('Y-m-d H:i:s'),
                'status'            => 'active',
            ]);
        });

        $this->assignDefaultRole((int)$row['user_id']);

        AuditService::instance()->log([
            'user_id'     => (int)$row['user_id'],
            'action'      => 'email_verified',
            'module'      => 'auth',
            'description' => 'Email verified for ' . $row['email'],
            'ip_address'  => request_ip(),
        ]);

        return ['success' => true, 'message' => 'Your email has been verified. You can now sign in.'];
    }

    public function resendVerification(string $email): array
    {
        $user = User::findByEmail($email);
        if ($user === null || $user['status'] !== 'pending') {
            return ['success' => false, 'message' => 'No pending account found for that email.'];
        }

        // Avoid flooding: regenerate token only if previous one is near expiry.
        $this->sendVerificationEmail((int)$user['id'], (string)$user['name'], (string)$user['email']);

        return ['success' => true, 'message' => 'A new verification email has been sent.'];
    }

    // -- Login / logout -------------------------------------------------------

    /**
     * @return array{success:bool,message:string,redirect:?string}
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        $ip = request_ip();

        $user = User::findByEmail($email);
        $maxAttempts = (int)setting('security.max_failed_attempts', 5);
        $lockoutMinutes = (int)setting('security.lockout_minutes', 15);

        if ($user === null) {
            LoginHistory::createEntry([
                'email'     => $email,
                'ip_address'=> $ip,
                'user_agent'=> substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
                'device_type'=> $this->detectDevice(),
                'browser'   => $this->detectBrowser(),
                'platform'  => $this->detectPlatform(),
                'status'    => 'failed',
                'reason'    => 'unknown_email',
            ]);

            return ['success' => false, 'message' => 'These credentials do not match our records.', 'redirect' => null];
        }

        // Lockout check
        $lockedUntil = $user['locked_until'] ?? null;
        if ($lockedUntil !== null && strtotime((string)$lockedUntil) > time()) {
            $minutes = (int)ceil((strtotime((string)$lockedUntil) - time()) / 60);
            LoginHistory::createEntry($this->context($user, 'lockout', "account_locked_{$minutes}m"));

            return ['success' => false, 'message' => "Too many failed attempts. Account locked for {$minutes} minute(s).", 'redirect' => null];
        }

        if (!password_verify($password, (string)$user['password'])) {
            $attempts = (int)$user['failed_attempts'] + 1;
            $data = ['failed_attempts' => $attempts];
            if ($attempts >= $maxAttempts) {
                $data['failed_attempts'] = 0;
                $data['locked_until'] = date('Y-m-d H:i:s', time() + $lockoutMinutes * 60);
                SecurityEvent::createEvent([
                    'user_id'    => (int)$user['id'],
                    'event_type' => 'account_locked',
                    'severity'   => 'critical',
                    'message'    => "Account locked after {$attempts} failed login attempts",
                    'ip_address' => $ip,
                    'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
                ]);
            }
            User::updateById((int)$user['id'], $data);

            LoginHistory::createEntry($this->context($user, 'failed', 'invalid_password'));

            return ['success' => false, 'message' => 'These credentials do not match our records.', 'redirect' => null];
        }

        // Correct password: reset counters, check status
        User::updateById((int)$user['id'], ['failed_attempts' => 0, 'locked_until' => null]);

        if ($user['status'] === 'pending') {
            $this->sendVerificationEmail((int)$user['id'], (string)$user['name'], (string)$user['email']);

            return ['success' => false, 'message' => 'Please verify your email address. A new verification link has been sent.', 'redirect' => null];
        }

        if ($user['status'] === 'inactive') {
            return ['success' => false, 'message' => 'Your account has been deactivated. Contact an administrator.', 'redirect' => null];
        }

        $this->establishSession((int)$user['id']);

        if ($remember && (bool)setting('security.remember_me', true)) {
            $this->issueRememberToken((int)$user['id']);
        }

        User::updateById((int)$user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip,
        ]);

        LoginHistory::createEntry($this->context($user, 'success', null));

        AuditService::instance()->log([
            'user_id'     => (int)$user['id'],
            'action'      => 'login',
            'module'      => 'auth',
            'description' => 'User logged in',
            'ip_address'  => $ip,
        ]);

        return [
            'success'  => true,
            'message'  => 'Welcome back!',
            'redirect' => $this->intendedRedirect(),
        ];
    }

    public function logout(): void
    {
        $userId = Session::getAuth();
        $user = $userId !== null ? User::find($userId) : null;

        if ($user !== null) {
            LoginHistory::createEntry($this->context($user, 'logout', null));
            AuditService::instance()->log([
                'user_id'    => (int)$user['id'],
                'action'     => 'logout',
                'module'     => 'auth',
                'description'=> 'User logged out',
                'ip_address' => request_ip(),
            ]);
        }

        $this->clearRememberCookie();
        Session::logout();
    }

    private function establishSession(int $userId): void
    {
        Session::setAuth($userId);

        $expiryDays = (int)setting('security.password_expiry_days', 0);
        if ($expiryDays > 0) {
            $user = User::find($userId);
            $changedAt = $user['password_changed_at'] ?? null;
            if ($changedAt === null || strtotime($changedAt) < time() - $expiryDays * 86400) {
                Session::set('_must_change_password', true);
            }
        }
    }

    private function intendedRedirect(): ?string
    {
        $intended = Session::get('_intended');
        Session::remove('_intended');

        if (is_string($intended) && str_starts_with($intended, 'http')) {
            // only allow same-host redirects (open-redirect protection)
            $host = parse_url($intended, PHP_URL_HOST);
            $currentHost = $_SERVER['HTTP_HOST'] ?? '';
            if ($host === $currentHost) {
                return $intended;
            }
        }

        return null;
    }

    // -- Remember me ----------------------------------------------------------

    private function issueRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        User::updateById($userId, ['remember_token' => hash('sha256', $token)]);

        $lifetime = 60 * 60 * 24 * 30;
        $secure = (bool)Config::get('session.secure', false);
        setcookie(self::REMEMBER_COOKIE, $token, [
            'expires'  => time() + $lifetime,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function attemptRemember(): void
    {
        $token = $_COOKIE[self::REMEMBER_COOKIE] ?? null;
        if ($token === null || Session::isAuthenticated()) {
            return;
        }
        if (!is_string($token) || strlen($token) < 40) {
            return;
        }

        $stmt = \App\Core\Database::pdo()->prepare(
            'SELECT `id` FROM `users` WHERE `remember_token` = :hash AND `status` = "active" LIMIT 1'
        );
        $stmt->execute([':hash' => hash('sha256', $token)]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            Session::setAuth((int)$id);
        } else {
            $this->clearRememberCookie();
        }
    }

    private function clearRememberCookie(): void
    {
        if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
            setcookie(self::REMEMBER_COOKIE, '', time() - 42000, '/');
            unset($_COOKIE[self::REMEMBER_COOKIE]);
        }
    }

    // -- Password resets ------------------------------------------------------

    public function forgot(string $email): void
    {
        $user = User::findByEmail($email);
        if ($user === null) {
            return; // Behave identically whether or not the account exists.
        }

        $token = random_token(48);
        PasswordReset::insertGetId([
            'email'      => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => gmdate('Y-m-d H:i:s', time() + 3600),
        ]);

        $link = url('/reset-password/' . $token);
        MailService::instance()->sendPasswordReset($email, (string)$user['name'], $link);

        AuditService::instance()->log([
            'user_id'     => (int)$user['id'],
            'action'      => 'password_reset_request',
            'module'      => 'auth',
            'description' => 'Password reset link requested',
            'ip_address'  => request_ip(),
        ]);
    }

    public function resetPassword(string $token, string $email, string $password, string $passwordConfirmation): array
    {
        $hash = hash('sha256', $token);
        $row = PasswordReset::validFor($email, $hash);

        if ($row === null || strtotime((string)$row['expires_at'] . ' UTC') < time()) {
            return ['success' => false, 'message' => 'This password reset link is invalid or has expired.'];
        }

        if ($password !== $passwordConfirmation) {
            return ['success' => false, 'message' => 'Password confirmation does not match.'];
        }

        $minLength = (int)setting('security.min_password_length', 8);
        if (strlen($password) < $minLength) {
            return ['success' => false, 'message' => "Password must be at least {$minLength} characters."];
        }

        $user = User::findByEmail($email);

        \App\Core\Database::transaction(function () use ($user, $password, $row, $email): void {
            if ($user !== null) {
                User::updateById((int)$user['id'], [
                    'password'            => $this->hash($password),
                    'password_changed_at' => date('Y-m-d H:i:s'),
                    'failed_attempts'     => 0,
                    'locked_until'        => null,
                ]);
            }
            PasswordReset::invalidateFor($email);
        });

        AuditService::instance()->log([
            'user_id'     => $user['id'] ?? null,
            'action'      => 'password_reset',
            'module'      => 'auth',
            'description' => 'Password was reset' . ($user === null ? ' (unknown email)' : ' for ' . $user['email']),
            'ip_address'  => request_ip(),
        ]);

        return ['success' => true, 'message' => 'Your password has been reset. Please sign in.'];
    }

    public function changePassword(int $userId, string $current, string $new, string $confirmation): array
    {
        $user = User::find($userId);
        if ($user === null) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if (!password_verify($current, (string)$user['password'])) {
            return ['success' => false, 'message' => 'Your current password is incorrect.'];
        }

        $minLength = (int)setting('security.min_password_length', 8);
        if (strlen($new) < $minLength) {
            return ['success' => false, 'message' => "New password must be at least {$minLength} characters."];
        }

        if ($new !== $confirmation) {
            return ['success' => false, 'message' => 'New password confirmation does not match.'];
        }

        if (password_verify($new, (string)$user['password'])) {
            return ['success' => false, 'message' => 'New password must be different from the current one.'];
        }

        User::updateById($userId, [
            'password'            => $this->hash($new),
            'password_changed_at' => date('Y-m-d H:i:s'),
        ]);

        Session::remove('_must_change_password');
        Session::regenerate();

        AuditService::instance()->log([
            'user_id'     => $userId,
            'action'      => 'password_changed',
            'module'      => 'auth',
            'description' => 'User changed their password',
            'ip_address'  => request_ip(),
        ]);

        return ['success' => true, 'message' => 'Password updated successfully.'];
    }

    // -- helpers --------------------------------------------------------------

    public function hash(string $password): string
    {
        $options = [];
        if (defined('PASSWORD_ARGON2ID')) {
            // argon2id is preferred when available; falls back to bcrypt.
            $options['memory_cost'] = 65536;
            $options['time_cost']   = 4;
            $options['threads']     = 2;

            return password_hash($password, PASSWORD_ARGON2ID, $options);
        }

        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function context(array $user, string $status, ?string $reason): array
    {
        return [
            'user_id'     => (int)$user['id'],
            'email'       => $user['email'] ?? null,
            'ip_address'  => request_ip(),
            'user_agent'  => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
            'device_type' => $this->detectDevice(),
            'browser'     => $this->detectBrowser(),
            'platform'    => $this->detectPlatform(),
            'status'      => $status,
            'reason'      => $reason,
        ];
    }

    private function detectDevice(): string
    {
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $ua)) {
            return 'mobile';
        }
        if (preg_match('/Tablet|iPad/i', $ua)) {
            return 'tablet';
        }

        return 'desktop';
    }

    private function detectBrowser(): string
    {
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $browsers = [
            'Edg/' => 'Edge', 'OPR/' => 'Opera', 'Chrome/' => 'Chrome',
            'Firefox/' => 'Firefox', 'Safari/' => 'Safari',
        ];
        foreach ($browsers as $key => $name) {
            if (str_contains($ua, $key)) {
                return $name;
            }
        }

        return 'unknown';
    }

    private function detectPlatform(): string
    {
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (str_contains($ua, 'Windows')) {
            return 'Windows';
        }
        if (str_contains($ua, 'Android')) {
            return 'Android';
        }
        if (str_contains($ua, 'iPhone')) {
            return 'iOS';
        }
        if (str_contains($ua, 'Mac OS')) {
            return 'macOS';
        }
        if (str_contains($ua, 'Linux')) {
            return 'Linux';

        }

        return 'unknown';
    }
}