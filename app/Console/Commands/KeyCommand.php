<?php

declare(strict_types=1);

namespace App\Console\Commands;

/**
 * Generates a random APP_KEY and replaces it in the .env file.
 */
final class KeyCommand
{
    public function execute(array $options): int
    {
        $envFile = ROOT_PATH . '/.env';
        if (!is_file($envFile)) {
            fwrite(STDERR, ".env file not found. Copy .env.example to .env first.\n");

            return 1;
        }

        $key = 'base64:' . base64_encode(random_bytes(32));
        $content = (string)file_get_contents($envFile);

        if (preg_match('/^APP_KEY=.*$/m', $content)) {
            $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY="' . $key . '"', $content);
        } else {
            $content = "APP_KEY=\"{$key}\"\n" . $content;
        }

        file_put_contents($envFile, $content, LOCK_EX);
        echo "APP_KEY generated and written to .env\n";

        return 0;
    }
}