<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Database;

/**
 * Applies pending .sql files under database/migrations in filename order.
 */
final class MigrateCommand
{
    public function execute(array $options): int
    {
        $pdo = Database::pdo();

        $pdo->exec('CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(190) NOT NULL,
            `applied_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_migrations_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        $applied = array_column($pdo->query('SELECT `name` FROM `migrations`')->fetchAll(), 'name');

        $files = glob(DATABASE_PATH . '/migrations/*.sql');
        if ($files === false || $files === []) {
            fwrite(STDERR, "No migration files found.\n");

            return 1;
        }
        sort($files);

        $ran = 0;
        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $applied, true)) {
                echo "  [skip] {$name}\n";
                continue;
            }

            $sql = (string)file_get_contents($file);
            $statements = $this->splitStatements($sql);

            echo "  [run]  {$name}\n";
            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }

            $stmt = $pdo->prepare('INSERT INTO `migrations` (`name`, `applied_at`) VALUES (:n, :t)');
            $stmt->execute([':n' => $name, ':t' => date('Y-m-d H:i:s')]);
            $ran++;
        }

        echo ($ran > 0 ? "\n" : '') . "Migrations complete. Applied {$ran} new migration(s).\n";

        return 0;
    }

    private function splitStatements(string $sql): array
    {
        $sql = preg_replace('/^--.*$/m', '', $sql);
        $parts = preg_split('/;(\s*\r?\n|$)/', $sql);
        if ($parts === false || $parts === []) {
            return [];
        }

        $statements = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $statements[] = $part;
            }
        }

        return $statements;
    }
}