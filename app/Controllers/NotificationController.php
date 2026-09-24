<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Models\Notification;

final class NotificationController extends Controller
{
    public function index(): string
    {
        $page = (int)$this->request->query('page', 1);
        $perPage = (int)setting('general.records_per_page', 20);

        $result = Notification::paginateForUser($this->userId(), $page, $perPage);

        return $this->view('notifications/index', [
            'pageTitle'     => 'Notifications',
            'notifications' => $result['items'],
            'pagination'    => $result,
        ]);
    }

    public function markRead(string $id)
    {
        $ok = NotificationService::instance()->markRead((int)$id, $this->userId());

        if ($this->request->isAjax()) {
            return $this->json(['success' => $ok]);
        }

        $this->redirect('/notifications');
    }

    public function markAllRead()
    {
        NotificationService::instance()->markAllRead($this->userId());

        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }

        $this->redirect('/notifications');
    }
}