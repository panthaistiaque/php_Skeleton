<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Filesystem + throwable logger with daily rotation.
 */
final class Logger
{
    private const DIRECTORY = STORAGE_PATH . '/logs';

    public static function write(string $level, string $message, array $context = []): void
    {
        if (!is_dir(self::DIRECTORY)) {
            @mkdir(self::DIRECTORY, 0775, true);
        }

        $line = sprintf(
            "[%s] %s.%-4s | %s%s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            (string)getmypid(),
            $message,
            $context ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );

        @file_put_contents(
            self::DIRECTORY . '/app-' . date('Y-m-d') . '.log',
            $line,
            FILE_APPEND | LOCK_EX
        );
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARN', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if ((bool)Config::get('app.debug', false)) {
            self::write('DEBUG', $message, $context);
        }
    }

    public static function exception(\Throwable $e): void
    {
        self::write('EXCEPTION', $e->getMessage(), [
            'class'   => $e::class,
            'file'    => $e->getFile() . ':' . $e->getLine(),
            'trace'   => substr($e->getTraceAsString(), 0, 2000),
            'request' => substr($_SERVER['REQUEST_URI'] ?? 'cli', 0, 500),
        ]);
    }

    public static function tail(int $lines = 500): string
    {
        $file = self::DIRECTORY . '/app-' . date('Y-m-d') . '.log';
        if (!is_file($file)) {
            return '';
        }

        $content = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $content = $content === false ? [] : $content;

        return implode("\n", array_slice($content, -$lines));
    }
}