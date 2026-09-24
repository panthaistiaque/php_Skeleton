<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Response;
use App\Models\ActivityLog;

/**
 * MySQL database backups: DDL + data dump, no external binary required.
 * Uses mysqldump when the binary path is configured (faster/lock-safe).
 */
final class BackupService
{
    private const DIR = STORAGE_PATH . '/backups';

    /**
     * @return array{file:string,path:string,size:int}
     */
    public function create(): array
    {
        if (!is_dir(self::DIR)) {
            @mkdir(self::DIR, 0775, true);
        }

        $filename = 'backup-' . date('Ymd-His') . '.sql';
        $path = self::DIR . '/' . $filename;

        $dump = $this->useMysqldump() ? $this->dumpViaMysqldump() : $this->dumpViaPhp();
        if ($dump === null || $dump === '') {
            throw new \RuntimeException('Backup failed: empty dump produced.');
        }

        file_put_contents($path, $dump, LOCK_EX);

        ActivityLog::createEntry([
            'user_id'    => \App\Core\Session::getAuth(),
            'action'     => 'backup_created',
            'module'     => 'system',
            'description'=> 'Database backup created: ' . $filename,
            'ip_address' => request_ip(),
        ]);

        return ['file' => $filename, 'path' => $path, 'size' => (int)filesize($path)];
    }

    public function list(): array
    {
        if (!is_dir(self::DIR)) {
            return [];
        }

        $files = glob(self::DIR . '/*.sql');
        if ($files === false) {
            return [];
        }

        $backups = [];
        foreach ($files as $file) {
            $backups[] = [
                'file' => basename($file),
                'path' => $file,
                'size' => (int)filesize($file),
                'date' => date('Y-m-d H:i:s', (int)filemtime($file)),
            ];
        }

        usort($backups, fn($a, $b) => $b['date'] <=> $a['date']);

        return $backups;
    }

    public function delete(string $filename): bool
    {
        $this->assertSafeFilename($filename);
        $path = self::DIR . '/' . $filename;
        if (is_file($path)) {
            return unlink($path);
        }

        return false;
    }

    public function download(string $filename): never
    {
        $this->assertSafeFilename($filename);
        Response::download(self::DIR . '/' . $filename, $filename);
    }

    // -- dump implementations -------------------------------------------------

    private function useMysqldump(): bool
    {
        $path = (string)Config::get('backup.mysqldump_path', '');

        return $path !== '' && is_file($path);
    }

    private function dumpViaMysqldump(): ?string
    {
        $cmd = escapeshellarg((string)Config::get('backup.mysqldump_path'));
        $db = Config::get('database');
        $cmd .= ' -u' . escapeshellarg((string)$db['username']) . ' --host=' . escapeshellarg((string)$db['host']) . ' --port=' . (int)$db['port'];
        if ((string)$db['password'] !== '') {
            $cmd .= ' -p' . escapeshellarg((string)$db['password']);
        }
        $cmd .= ' --single-transaction --routines --triggers ' . escapeshellarg((string)$db['database']);

        $output = [];
        $exitCode = 0;
        exec($cmd . ' 2>&1', $output, $exitCode);

        return $exitCode === 0 ? implode("\n", $output) : null;
    }

    private function dumpViaPhp(): string
    {
        $pdo = Database::pdo();
        $database = (string)(Config::get('database.database') ?? '');
        $sql = [];

        $sql[] = "-- Skeleton App backup generated " . date('Y-m-d H:i:s');
        $sql[] = "-- Database: {$database}";
        $sql[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $sql[] = 'SET NAMES utf8mb4;';
        $sql[] = '';

        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $sql[] = '-- ------------------------------------------------------------';
            $sql[] = '-- Table structure for `' . $table . '`';
            $sql[] = '-- ------------------------------------------------------------';
            $sql[] = 'DROP TABLE IF EXISTS `' . $table . '`;';
            $sql[] = '';

            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createStmt = $create['Create Table'] ?? reset($create);
            $sql[] = $createStmt . ';';
            $sql[] = '';

            $total = (int)$pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            if ($total === 0) {
                continue;
            }

            $sql[] = '-- ------------------------------------------------------------';
            $sql[] = '-- Dumping data for `' . $table . '`';
            $sql[] = '-- ------------------------------------------------------------';

            $offset = 0;
            $chunk = 500;
            while ($offset < $total) {
                $rows = $pdo->query("SELECT * FROM `{$table}` LIMIT {$chunk} OFFSET {$offset}")->fetchAll();
                foreach ($rows as $row) {
                    $columns = array_keys($row);
                    $values = [];
                    foreach ($row as $value) {
                        $values[] = $value === null ? 'NULL' : $pdo->quote((string)$value);
                    }
                    $sql[] = 'INSERT INTO `' . $table . '` (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $values) . ');';
                }
                $offset += $chunk;
            }
            $sql[] = '';
        }

        $sql[] = 'SET FOREIGN_KEY_CHECKS=1;';

        return implode("\n", $sql);
    }

    private function assertSafeFilename(string $filename): void
    {
        if (basename($filename) !== $filename || !preg_match('/^backup-\d{14}\.sql$/', $filename)) {
            throw new \App\Core\HttpException('Invalid backup filename.', 400);
        }
    }
}