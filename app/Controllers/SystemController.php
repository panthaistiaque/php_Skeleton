<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Logger;
use App\Services\BackupService;

final class SystemController extends Controller
{
    public function health(): string
    {
        $checks = [];

        // Database connectivity + version
        try {
            $version = \App\Core\Database::pdo()->query('SELECT VERSION()')->fetchColumn();
            $checks['database'] = ['ok' => true, 'label' => 'Database', 'detail' => (string)$version];
        } catch (\Throwable $e) {
            $checks['database'] = ['ok' => false, 'label' => 'Database', 'detail' => $e->getMessage()];
        }

        // Storage writability
        $storageWritable = is_writable(STORAGE_PATH) && is_writable(STORAGE_PATH . '/logs');
        $checks['storage'] = ['ok' => $storageWritable, 'label' => 'Storage Writable', 'detail' => STORAGE_PATH];

        // Disk space
        $free = function_exists('disk_free_space') ? disk_free_space(STORAGE_PATH) : false;
        $checks['disk'] = [
            'ok'     => $free === false || $free > 1024 * 1024 * 10,
            'label'  => 'Disk Space',
            'detail' => $free === false ? 'unknown' : bytes_to_human((int)$free) . ' free',
        ];

        // Session configuration
        $sessionSecure = (bool)config('session.secure', false);
        $checks['session'] = ['ok' => true, 'label' => 'Secure Session', 'detail' => $sessionSecure ? 'Secure cookies enabled' : 'Secure cookies disabled (set SESSION_SECURE=true in production)'];

        // SMTP
        try {
            $smtp = \App\Services\MailService::instance()->smtpConfig();
            $checks['mail'] = [
                'ok'     => true,
                'label'  => 'Mail',
                'detail' => $smtp['enabled'] ? ("SMTP " . $smtp['host'] . ':' . $smtp['port']) : 'SMTP disabled (emails are logged)',
            ];
        } catch (\Throwable $e) {
            $checks['mail'] = ['ok' => false, 'label' => 'Mail', 'detail' => $e->getMessage()];
        }

        // Required extensions
        $required = ['pdo_mysql', 'openssl', 'mbstring', 'json', 'session'];
        $missing = array_filter($required, fn($ext) => !extension_loaded($ext));
        $checks['extensions'] = ['ok' => $missing === [], 'label' => 'PHP Extensions', 'detail' => $missing === [] ? implode(', ', $required) : 'Missing: ' . implode(', ', $missing)];

        $checks['app'] = ['ok' => true, 'label' => 'Application', 'detail' => config('app.name', 'Skeleton App') . ' v' . config('app.version', '1.0.0')];

        $allOk = !in_array(false, array_column($checks, 'ok'), true);

        return $this->view('system/health', [
            'pageTitle' => 'System Health',
            'checks'    => $checks,
            'allOk'     => $allOk,
        ]);
    }

    public function backups(): string
    {
        return $this->view('system/backups', [
            'pageTitle' => 'Database Backups',
            'backups'   => (new BackupService())->list(),
        ]);
    }

    public function createBackup()
    {
        try {
            $backup = (new BackupService())->create();
            $this->flashSuccess('Backup created: ' . $backup['file'] . ' (' . bytes_to_human($backup['size']) . ')');
        } catch (\Throwable $e) {
            Logger::error('Backup failed: ' . $e->getMessage());
            $this->flashError('Backup failed: ' . $e->getMessage());
        }

        $this->redirect('/system/backups');
    }

    public function downloadBackup(string $file)
    {
        (new BackupService())->download($file);
    }

    public function destroyBackup(string $file)
    {
        try {
            (new BackupService())->delete($file);
            $this->flashSuccess('Backup deleted.');
        } catch (\Throwable $e) {
            $this->flashError($e->getMessage());
        }

        $this->redirect('/system/backups');
    }

    public function logs(): string
    {
        $raw = Logger::tail(1500);

        return $this->view('system/logs', [
            'pageTitle' => 'Application Logs',
            'logs'      => explode("\n", $raw === '' ? 'No log entries yet.' : $raw),
            'logPath'   => STORAGE_PATH . '/logs',
        ]);
    }
}