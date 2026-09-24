<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;

/**
 * In-app notification helpers.
 */
final class NotificationService
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function notifyUser(int $userId, string $title, string $body = '', string $type = 'info'): void
    {
        Notification::create($title, $body, $userId, $type);
    }

    public function notifyAll(string $title, string $body = '', string $type = 'info'): void
    {
        Notification::create($title, $body, 0, $type);
    }

    public function listRecent(int $userId, int $limit = 30): array
    {
        return Notification::forUser($userId, $limit);
    }

    public function unreadCount(int $userId): int
    {
        return Notification::unreadCountFor($userId);
    }

    public function markRead(int $id, int $userId): bool
    {
        $row = Notification::find($id);
        if ($row === null) {
            return false;
        }
        if ($row['user_id'] !== null && (int)$row['user_id'] !== $userId) {
            return false;
        }

        Notification::updateById($id, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);

        return true;
    }

    public function markAllRead(int $userId): void
    {
        Notification::markAllReadFor($userId);
    }
}