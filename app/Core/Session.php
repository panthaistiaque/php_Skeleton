<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Hardened native PHP sessions + flash messaging.
 *
 * - HttpOnly cookies, SameSite + (optional) Secure flags
 * - fixed session lifetime from config/security setting
 * - automatic idle timeout
 * - id regeneration on privilege elevation
 */
final class Session
{
    private const FLASH_KEY = '_skeleton_flash';
    private const OLD_KEY   = '_skeleton_old';
    private const AUTH_KEY  = '_skeleton_auth';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $name = (string)Config::get('session.name', 'SKEL_APP');
        $lifetime = (int)Config::get('session.lifetime', 7200);
        $secure = (bool)self::envBool('session.secure', false);
        $httpOnly = (bool)self::envBool('session.http_only', true);
        $sameSite = (string)Config::get('session.same_site', 'Lax');

        session_name($name);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ]);

        session_start();

        // Auto logout on idle timeout.
        $timeoutSeconds = ((int)setting('security.session_timeout', 30)) * 60;
        if ($timeoutSeconds > 0 && isset($_SESSION['_last_activity'])) {
            if ((time() - (int)$_SESSION['_last_activity']) > $timeoutSeconds) {
                self::destroy();
                session_start();
            }
        }
        $_SESSION['_last_activity'] = time();

        // Absolute lifetime cap (also handled by cookie lifetime).
        if (isset($_SESSION['_started_at']) && time() - (int)$_SESSION['_started_at'] > $lifetime) {
            self::destroy();
            session_start();
        }
        $_SESSION['_started_at'] = $_SESSION['_started_at'] ?? time();
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    // -- Authentication helpers -------------------------------------------------

    public static function setAuth(int $userId): void
    {
        self::regenerate();
        $_SESSION[self::AUTH_KEY] = $userId;
    }

    public static function getAuth(): ?int
    {
        $id = $_SESSION[self::AUTH_KEY] ?? null;
        return is_numeric($id) ? (int)$id : null;
    }

    public static function isAuthenticated(): bool
    {
        return self::getAuth() !== null;
    }

    public static function logout(): void
    {
        self::destroy();
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function regenerate(bool $deleteOld = true): void
    {
        session_regenerate_id($deleteOld);
    }

    // -- Flash messages ---------------------------------------------------------

    public static function flash(string $key, mixed $value): void
    {
        if (!isset($_SESSION[self::FLASH_KEY])) {
            $_SESSION[self::FLASH_KEY] = [];
        }
        $_SESSION[self::FLASH_KEY][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[self::FLASH_KEY][$key] ?? $default;
        unset($_SESSION[self::FLASH_KEY][$key]);

        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION[self::FLASH_KEY][$key]);
    }

    public static function flushAllFlash(): void
    {
        unset($_SESSION[self::FLASH_KEY]);
    }

    public static function withOld(array $input): void
    {
        $_SESSION[self::OLD_KEY] = $input;
    }

    public static function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION[self::OLD_KEY][$key] ?? $default;
    }

    public static function clearOld(): void
    {
        unset($_SESSION[self::OLD_KEY]);
    }

    private static function envBool(string $key, bool $default): bool
    {
        $value = Config::get($key, $default);

        return is_bool($value)
            ? $value
            : (is_string($value) ? strtolower($value) === 'true' || $value === '1' : (bool)$value);
    }
}