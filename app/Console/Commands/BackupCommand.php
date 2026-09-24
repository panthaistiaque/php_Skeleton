<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupService;

/**
 * php console backup
 */
final class BackupCommand
{
    public function execute(array $options): int
    {
        $backup = (new BackupService())->create();

        echo "Backup created: {$backup['file']} (" . $backup['size'] . " bytes)\n";

        return 0;
    }
}