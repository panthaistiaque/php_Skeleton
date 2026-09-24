<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\User;
use App\Services\SettingsService;

/**
 * Global helper functions used across the whole application.
 */

if (!function_exists('e')) {
    /** Escape a string for safe HTML output (XSS prevention). */
    function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('h')) {
    function h(mixed $value): string
    {
        return e($value);
    }
}

if (!function_exists('class_basename')) {
    function class_basename(string $class): string
    {
        return substr($class, (int)strrpos($class, '\\') + 1);
    }
}

if (!function_exists('url')) {
    /** Generate an application URL from an internal path, e.g. url(/users/edit/1). */
    function url(string $path = ''): string
    {
        return base_url($path);
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $base = (string)Config::get('app.url', '');

        if ($base === '') {
            $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/'));
            $base = rtrim(dirname(dirname($script)), '/');
            if ($base === '' || $base === '/') {
                $base = '';
            }
        }

        $base = rtrim($base, '/');
        if ($path !== '') {
            return $base . '/' . ltrim($path, '/');
        }

        return $base;
    }
}

if (!function_exists('asset')) {
    /** URL for a file inside /public/assets. */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return \App\Core\Env::get($key, $default);
    }
}

if (!function_exists('setting')) {
    /** Read an application setting (from DB-backed settings store). */
    function setting(string $key, mixed $default = null): mixed
    {
        return SettingsService::instance()->get($key, $default);
    }
}

if (!function_exists('auth')) {
    /** Signed-in user row (array) or null. */
    function auth(): ?array
    {
        $id = Session::getAuth();

        return $id !== null ? User::find((int)$id) : null;
    }
}

if (!function_exists('user_can')) {
    function user_can(string $permission): bool
    {
        return \App\Services\PermissionService::instance()->has($permission);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('old')) {
    /** Old input value after a failed validation. */
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::old($key, $default);
    }
}

if (!function_exists('flash_get')) {
    function flash_get(string $key, mixed $default = null): mixed
    {
        return Session::getFlash($key, $default);
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e($method) . '">';
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = ''): string
    {
        if ($date === null || $date === '') {
            return '—';
        }
        $format = $format !== '' ? $format : (string)setting('general.date_format', 'Y-m-d');
        $timestamp = strtotime($date);

        return $timestamp === false ? '—' : date($format, $timestamp);
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $date, string $format = ''): string
    {
        if ($date === null || $date === '') {
            return '—';
        }
        $format = $format !== '' ? $format : (string)setting('general.datetime_format', 'Y-m-d H:i:s');
        $timestamp = strtotime($date);

        return $timestamp === false ? '—' : date($format, $timestamp);
    }
}

if (!function_exists('time_ago')) {
    function time_ago(?string $date): string
    {
        if ($date === null || $date === '') {
            return '—';
        }
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return '—';
        }
        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'just now';
        }
        $units = [
            31536000 => 'year', 2592000 => 'month', 86400 => 'day',
            3600 => 'hour', 60 => 'minute',
        ];
        foreach ($units as $seconds => $unit) {
            if ($diff < $seconds) {
                continue;
            }
            $value = (int)floor($diff / $seconds);

            return $value . ' ' . $unit . ($value > 1 ? 's' : '') . ' ago';
        }

        return 'just now';
    }
}

if (!function_exists('bytes_to_human')) {
    function bytes_to_human(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        $value = (float)$bytes;
        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        return round($value, $i === 0 ? 0 : 2) . ' ' . $units[$i];
    }
}

if (!function_exists('random_token')) {
    function random_token(int $length = 64): string
    {
        return bin2hex(random_bytes((int)ceil($length / 2)));
    }
}

if (!function_exists('request_ip')) {
    /** Best-effort client IP. */
    function request_ip(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        return (string)filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
    }
}

if (!function_exists('request_user_agent')) {
    function request_user_agent(): string
    {
        return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
    }
}

if (!function_exists('validation_error')) {
    /** First validation error message for a field (one-shot read). */
    function validation_error(string $field): string
    {
        $errors = Session::getFlash('validation.' . $field, []);

        return is_array($errors) && $errors !== [] ? implode(' ', array_map('strval', $errors)) : '';
    }
}

if (!function_exists('has_validation_error')) {
    function has_validation_error(string $field): bool
    {
        return Session::hasFlash('validation.' . $field);
    }
}

if (!function_exists('setting_badge')) {
    /** Render a yes/no badge for boolean-like values. */
    function setting_badge(mixed $value): string
    {
        $on = $value === true || $value === '1' || $value === 1 || $value === 'true';

        return $on
            ? '<span class="badge bg-success">Enabled</span>'
            : '<span class="badge bg-secondary">Disabled</span>';
    }
}

if (!function_exists('status_badge')) {
    /** Bootstrap badge for user/login status values. */
    function status_badge(string $status): string
    {
        $map = [
            'active'   => 'success',
            'inactive' => 'secondary',
            'pending'  => 'warning',
            'suspended'=> 'danger',
            'success'  => 'success',
            'failed'   => 'danger',
            'lockout'  => 'danger',
            'logout'   => 'secondary',
            'info'     => 'info',
            'warning'  => 'warning',
            'critical' => 'danger',
        ];
        $class = $map[$status] ?? 'secondary';

        return '<span class="badge bg-' . $class . '">' . e(ucfirst($status)) . '</span>';
    }
}

if (!function_exists('render_flash_alerts')) {
    /** Renders one-shot Bootstrap alert elements for flash messages. */
    function render_flash_alerts(): string
    {
        $html = '';
        foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info', 'warning' => 'warning'] as $key => $bootstrap) {
            $message = Session::getFlash($key);
            if ($message !== null && $message !== '') {
                $html .= '<div class="alert alert-' . $bootstrap . ' alert-dismissible fade show" role="alert">'
                    . e(is_array($message) ? implode(' ', array_map('strval', $message)) : (string)$message)
                    . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }
        }

        return $html;
    }
}