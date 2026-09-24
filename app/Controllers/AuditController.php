<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Repositories\AuditRepository;

final class AuditController extends Controller
{
    private function page(): int
    {
        return (int)($this->request->query('page', 1));
    }

    private function perPage(): int
    {
        return (int)setting('general.records_per_page', 20);
    }

    public function logins(): string
    {
        $filters = [
            'search' => (string)$this->request->query('search', ''),
            'status' => (string)$this->request->query('status', ''),
            'user_id'=> (string)$this->request->query('user_id', ''),
            'from'   => (string)$this->request->query('from', ''),
            'to'     => (string)$this->request->query('to', ''),
        ];

        return $this->view('audit/logins', [
            'pageTitle'  => 'Login History',
            'rows'       => AuditRepository::loginHistory($filters, $this->page(), $this->perPage()),
            'filters'    => $filters,
        ]);
    }

    public function failedLogins(): string
    {
        $filters = [
            'search' => (string)$this->request->query('search', ''),
            'status' => 'failed',
            'user_id'=> (string)$this->request->query('user_id', ''),
            'from'   => (string)$this->request->query('from', ''),
            'to'     => (string)$this->request->query('to', ''),
        ];

        return $this->view('audit/logins', [
            'pageTitle' => 'Failed Login Attempts',
            'rows'      => AuditRepository::loginHistory(['status' => 'failed'] + $filters, $this->page(), $this->perPage()),
            'filters'   => $filters,
            'failedOnly'=> true,
        ]);
    }

    public function activities(): string
    {
        $filters = [
            'search' => (string)$this->request->query('search', ''),
            'action' => (string)$this->request->query('action', ''),
            'user_id'=> (string)$this->request->query('user_id', ''),
            'from'   => (string)$this->request->query('from', ''),
            'to'     => (string)$this->request->query('to', ''),
        ];

        return $this->view('audit/activities', [
            'pageTitle' => 'Activity Logs',
            'rows'      => AuditRepository::activities($filters, $this->page(), $this->perPage()),
            'filters'   => $filters,
        ]);
    }

    public function security(): string
    {
        $filters = [
            'search'  => (string)$this->request->query('search', ''),
            'severity'=> (string)$this->request->query('severity', ''),
            'from'    => (string)$this->request->query('from', ''),
            'to'      => (string)$this->request->query('to', ''),
        ];

        return $this->view('audit/security', [
            'pageTitle' => 'Security Events',
            'rows'      => AuditRepository::securityEvents($filters, $this->page(), $this->perPage()),
            'filters'   => $filters,
        ]);
    }

    public function work(string $id): string
    {
        $user = \App\Models\User::find((int)$id);
        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        return $this->view('audit/work', [
            'pageTitle'   => 'Work Tracking: ' . $user['name'],
            'user'        => $user,
            'timeline'    => AuditRepository::userWorkTimeline((int)$user['id'], 500),
            'logins'      => AuditRepository::loginHistory(['user_id' => (string)$user['id']], 1, 50)['items'],
        ]);
    }
}