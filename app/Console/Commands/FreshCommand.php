<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Database;

/**
 * Drops all application tables, re-runs migrations and seeds.
 */
final class FreshCommand
{
    public function execute(array $options): int
    {
        $pdo = Database::pdo();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

        if ($tables !== []) {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
            foreach ($tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
            echo "Dropped " . count($tables) . " table(s).\n";
        }

        (new MigrateCommand())->execute($options);

        return (new SeedCommand())->execute($options);
    }
}