<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Repositories\ReportRepository;

final class ReportController extends Controller
{
    public function index(): string
    {
        return $this->view('reports/index', [
            'pageTitle' => 'Reports',
        ]);
    }

    public function exportUsers()
    {
        $filters = [
            'status' => (string)$this->request->query('status', ''),
            'from'   => (string)$this->request->query('from', ''),
            'to'     => (string)$this->request->query('to', ''),
        ];

        $rows = ReportRepository::users($filters);
        $headers = ['ID', 'Name', 'Email', 'Status', 'Roles', 'Last Login', 'Created At'];

        $this->outputCsv('users-report-' . date('Ymd-His') . '.csv', $headers, $rows, [
            'id', 'name', 'email', 'status', 'roles', 'last_login_at', 'created_at',
        ]);
    }

    public function exportLogins()
    {
        $filters = [
            'status' => (string)$this->request->query('status', ''),
            'from'   => (string)$this->request->query('from', ''),
            'to'     => (string)$this->request->query('to', ''),
        ];

        $rows = ReportRepository::logins($filters);
        $headers = ['ID', 'User', 'Email', 'IP', 'Device', 'Browser', 'Platform', 'Status', 'Reason', 'Time'];

        $this->outputCsv('logins-report-' . date('Ymd-His') . '.csv', $headers, $rows, [
            'id', 'user_name', 'email', 'ip_address', 'device_type', 'browser', 'platform', 'status', 'reason', 'created_at',
        ]);
    }

    public function exportActivities()
    {
        $filters = [
            'action' => (string)$this->request->query('action', ''),
            'from'   => (string)$this->request->query('from', ''),
            'to'     => (string)$this->request->query('to', ''),
        ];

        $rows = ReportRepository::activities($filters);
        $headers = ['ID', 'User', 'Action', 'Module', 'Description', 'Method', 'URL', 'IP', 'Time'];

        $this->outputCsv('activities-report-' . date('Ymd-His') . '.csv', $headers, $rows, [
            'id', 'user_name', 'action', 'module', 'description', 'method', 'url', 'ip_address', 'created_at',
        ]);
    }

    private function outputCsv(string $filename, array $headers, array $rows, array $fieldMap): never
    {
        $handle = fopen('php://temp', 'w');
        fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            $line = [];
            foreach ($fieldMap as $field) {
                $line[] = $row[$field] ?? '';
            }
            fputcsv($handle, $line);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        echo Response::make((string)$content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache',
            'Pragma'              => 'public',
        ]);
        exit;
    }
}