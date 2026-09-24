<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Database;

/**
 * Runs database/seeds/seed.sql then in-code seeders.
 */
final class SeedCommand
{
    public function execute(array $options): int
    {
        $pdo = Database::pdo();

        $seedFile = DATABASE_PATH . '/seeds/seed.sql';
        if (is_file($seedFile)) {
            echo "  [seed] " . basename($seedFile) . "\n";
            $sql = (string)file_get_contents($seedFile);
            foreach ($this->splitStatements($sql) as $statement) {
                $pdo->exec($statement);
            }
        }

        echo "  [seed] register_seeders\n";
        $seeder = new \App\Console\Seeders\MainSeeder();
        $seeder->run();

        echo "\nSeed data complete.\n";

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