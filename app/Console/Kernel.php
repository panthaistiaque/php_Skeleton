<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\AdminCommand;
use App\Console\Commands\BackupCommand;
use App\Console\Commands\FreshCommand;
use App\Console\Commands\KeyCommand;
use App\Console\Commands\MigrateCommand;
use App\Console\Commands\SeedCommand;

final class Kernel
{
    public static function run(array $argv): int
    {
        $command = $argv[1] ?? 'help';
        $options = self::parseOptions(array_slice($argv, 2));

        try {
            return match ($command) {
                'migrate'      => (new MigrateCommand())->execute($options),
                'seed'         => (new SeedCommand())->execute($options),
                'fresh'        => (new FreshCommand())->execute($options),
                'admin:create' => (new AdminCommand())->execute($options),
                'backup'       => (new BackupCommand())->execute($options),
                'key:generate' => (new KeyCommand())->execute($options),
                'help', '-h', '--help' => self::help(),
                default        => self::unknown($command),
            };
        } catch (\Throwable $e) {
            fwrite(STDERR, "\nERROR: " . $e->getMessage() . "\n\n");
            if (getenv('APP_DEBUG')) {
                fwrite(STDERR, $e->getTraceAsString() . "\n");
            }

            return 1;
        }
    }

    private static function parseOptions(array $args): array
    {
        $options = [];
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                $arg = substr($arg, 2);
                $parts = explode('=', $arg, 2);
                $options[$parts[0]] = $parts[1] ?? true;
            }
        }

        return $options;
    }

    private static function help(): int
    {
        echo <<<HELP

Skeleton App Console
====================

Available commands:

  migrate                 Run pending database migrations
  seed                    Insert seed data (permissions, roles, menus, settings)
  fresh                   Drop all tables, migrate, then seed
  admin:create            Create a super administrator user
                          --email=...  --password=...  --name=...
  backup                  Create a database backup in storage/backups
  key:generate            Write a fresh APP_KEY into the .env file

HELP;

        return 0;
    }

    private static function unknown(string $command): int
    {
        fwrite(STDERR, "Unknown command: {$command}\nRun `php console help` for available commands.\n");

        return 1;
    }
}