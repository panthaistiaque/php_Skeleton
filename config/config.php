<?php

declare(strict_types=1);

use App\Core\Env;

$debug = filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL);

return [

    'app' => [
        'name'      => Env::get('APP_NAME', 'Skeleton App'),
        'env'       => Env::get('APP_ENV', 'local'),
        'debug'     => $debug,
        'url'       => Env::get('APP_URL', ''),
        'timezone'  => Env::get('APP_TIMEZONE', 'UTC'),
        'key'       => Env::get('APP_KEY', ''),
        'version'   => '1.0.0',
    ],

    'database' => [
        'driver'   => Env::get('DB_CONNECTION', 'mysql'),
        'host'     => Env::get('DB_HOST', '127.0.0.1'),
        'port'     => Env::get('DB_PORT', '3306'),
        'database' => Env::get('DB_DATABASE', 'skeleton_app'),
        'username' => Env::get('DB_USERNAME', 'root'),
        'password' => Env::get('DB_PASSWORD', ''),
        'charset'  => Env::get('DB_CHARSET', 'utf8mb4'),
    ],

    'session' => [
        'name'      => Env::get('SESSION_NAME', 'SKEL_APP'),
        'lifetime'  => (int)Env::get('SESSION_LIFETIME', 7200),
        'secure'    => filter_var(Env::get('SESSION_SECURE', 'false'), FILTER_VALIDATE_BOOL),
        'http_only' => filter_var(Env::get('SESSION_HTTP_ONLY', 'true'), FILTER_VALIDATE_BOOL),
        'same_site' => Env::get('SESSION_SAME_SITE', 'Lax'),
    ],

    'mail' => [
        'enabled'    => filter_var(Env::get('SMTP_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'host'       => Env::get('SMTP_HOST', ''),
        'port'       => (int)Env::get('SMTP_PORT', 587),
        'username'   => Env::get('SMTP_USERNAME', ''),
        'password'   => Env::get('SMTP_PASSWORD', ''),
        'encryption' => Env::get('SMTP_ENCRYPTION', 'tls'),
        'from_email' => Env::get('SMTP_FROM_EMAIL', 'noreply@local.test'),
        'from_name'  => Env::get('SMTP_FROM_NAME', 'Skeleton App'),
    ],

    'backup' => [
        'mysqldump_path' => Env::get('MYSQLDUMP_PATH', ''),
    ],

    // Fallback defaults; real values are managed in DB settings.
    'default_settings' => [
        'security.min_password_length' => 8,
        'security.max_failed_attempts' => 5,
        'security.lockout_minutes'     => 15,
        'security.session_timeout'     => 30,
        'security.require_verification'=> true,
        'security.register_enabled'    => true,
        'security.remember_me'         => true,
        'security.password_expiry_days'=> 0,
        'general.timezone'             => (string)Env::get('APP_TIMEZONE', 'UTC'),
        'general.date_format'          => 'd M Y',
        'general.datetime_format'      => 'd M Y, h:i A',
        'general.records_per_page'     => 20,
        'audit.log_activities'         => true,
        'audit.track_page_views'       => false,
        'audit.log_security_events'    => true,
        'app.name'                     => (string)Env::get('APP_NAME', 'Skeleton App'),
    ],
];