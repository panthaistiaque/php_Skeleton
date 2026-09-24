<?php

declare(strict_types=1);

namespace App\Core;

/**
 * PHP view renderer (plain templates, no templating engine).
 */
final class View
{
    private static array $shared = [];

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        $content = self::make($view, $data);
        if ($layout === null || $layout === '') {
            return $content;
        }

        return self::make($layout, array_merge($data, ['content' => $content]));
    }

    private static function make(string $view, array $data): string
    {
        $file = VIEW_PATH . '/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View [{$view}] not found at {$file}");
        }

        extract(array_merge(self::$shared, $data), EXTR_SKIP);

        ob_start();
        try {
            include $file;

            return (string)ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
}