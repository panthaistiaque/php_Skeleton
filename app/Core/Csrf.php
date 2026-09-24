<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF token generation + validation.
 */
final class Csrf
{
    private const TOKEN_KEY = '_csrf_token';
    private const TIME_KEY  = '_csrf_time';

    public static function token(): string
    {
        if (!Session::has(self::TOKEN_KEY) || empty(Session::get(self::TOKEN_KEY))) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
            Session::set(self::TIME_KEY, time());
        }

        return (string)Session::get(self::TOKEN_KEY);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">';
    }

    /**
     * Validates a submitted token. An optional TTL (hours) forces rotation.
     */
    public static function validate(?string $submitted = null, int $tokenTtlHours = 24): bool
    {
        $submitted = $submitted ?? ($_POST['_token'] ?? null);
        if (!is_string($submitted) || $submitted === '') {
            return false;
        }

        if (!hash_equals(self::token(), $submitted)) {
            return false;
        }

        $issuedAt = (int)Session::get(self::TIME_KEY, 0);
        if ($tokenTtlHours > 0 && $issuedAt > 0 && (time() - $issuedAt) > $tokenTtlHours * 3600) {
            // Force token rotation by clearing it.
            Session::remove(self::TOKEN_KEY);
            Session::remove(self::TIME_KEY);

            return false;
        }

        return true;
    }

    public static function regenerate(): void
    {
        Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        Session::set(self::TIME_KEY, time());
    }
}