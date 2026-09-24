<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Notification extends Model
{
    protected string $table = 'notifications';
    protected array $fillable = ['user_id', 'type', 'title', 'body', 'is_read', 'read_at'];
    protected bool $timestamps = false;

    public static function forUser(int $userId, int $limit = 50): array
    {
        $model = new self();
        $stmt = $model->db()->prepare(
            'SELECT * FROM `notifications`
             WHERE (`user_id` = :uid OR `user_id` IS NULL)
             ORDER BY `created_at` DESC, `id` DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return array{items:array,total:int,page:int,per_page:int,last_page:int}
     */
    public static function paginateForUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        $model = new self();
        $where = '(`user_id` = :uid OR `user_id` IS NULL)';
        $perPage = max(1, (int)$perPage);
        $page = max(1, (int)$page);

        $countStmt = $model->db()->prepare("SELECT COUNT(*) FROM `notifications` WHERE {$where}");
        $countStmt->execute([':uid' => $userId]);
        $total = (int)$countStmt->fetchColumn();

        $lastPage = max(1, (int)ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $model->db()->prepare(
            "SELECT * FROM `notifications` WHERE {$where}
             ORDER BY `created_at` DESC, `id` DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'     => $stmt->fetchAll(),
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ];
    }

    public static function unreadCountFor(int $userId): int
    {
        $model = new self();
        $stmt = $model->db()->prepare(
            'SELECT COUNT(*) FROM `notifications`
             WHERE (`user_id` = :uid OR `user_id` IS NULL) AND `is_read` = 0'
        );
        $stmt->execute([':uid' => $userId]);

        return (int)$stmt->fetchColumn();
    }

    public static function markAllReadFor(int $userId): void
    {
        $model = new self();
        $stmt = $model->db()->prepare(
            'UPDATE `notifications` SET `is_read` = 1, `read_at` = :now
             WHERE (`user_id` = :uid OR `user_id` IS NULL) AND `is_read` = 0'
        );
        $stmt->execute([':uid' => $userId, ':now' => date('Y-m-d H:i:s')]);
    }

    public static function create(string $title, string $body = '', int $userId = 0, string $type = 'info'): int
    {
        $insert = self::insertGetId([
            'user_id' => $userId === 0 ? null : $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
        ]);

        return (int)$insert;
    }
}