<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Read-side queries for the audit module.
 */
final class AuditRepository
{
    /**
     * @return array{items:array,total:int,page:int,per_page:int,last_page:int}
     */
    public static function paginate(string $table, array $joins, array $filters, int $page = 1, int $perPage = 20): array
    {
        $pdo = Database::pdo();
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(x.email LIKE :search OR x.ip_address LIKE :search OR x.user_agent LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['user_id'])) {
            $where[] = 'x.user_id = :uid';
            $params[':uid'] = (int)$filters['user_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'x.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['action'])) {
            $where[] = 'x.action = :action';
            $params[':action'] = $filters['action'];
        }
        if (!empty($filters['severity'])) {
            $where[] = 'x.severity = :severity';
            $params[':severity'] = $filters['severity'];
        }
        if (!empty($filters['from'])) {
            $where[] = 'x.created_at >= :from';
            $params[':from'] = $filters['from'] . ' 00:00:00';
        }
        if (!empty($filters['to'])) {
            $where[] = 'x.created_at <= :to';
            $params[':to'] = $filters['to'] . ' 23:59:59';
        }
        if (!empty($filters['min_id'])) {
            $where[] = 'x.id >= :min_id';
            $params[':min_id'] = (int)$filters['min_id'];
        }

        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';
        $joinSql = implode(' ', $joins);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` x {$joinSql}{$whereSql}");
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        $lastPage = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;

        $select = 'x.*, u.name AS user_name, u.email AS user_email';
        $sql = "SELECT {$select} FROM `{$table}` x {$joinSql}{$whereSql}
                ORDER BY x.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
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

    public static function loginHistory(array $filters, int $page = 1, int $perPage = 20): array
    {
        $join = ['LEFT JOIN `users` u ON u.id = x.user_id'];

        return self::paginate('login_history', $join, $filters, $page, $perPage);
    }

    public static function activities(array $filters, int $page = 1, int $perPage = 20): array
    {
        $join = ['LEFT JOIN `users` u ON u.id = x.user_id'];

        return self::paginate('activities', $join, $filters, $page, $perPage);
    }

    public static function securityEvents(array $filters, int $page = 1, int $perPage = 20): array
    {
        $join = ['LEFT JOIN `users` u ON u.id = x.user_id'];

        return self::paginate('security_events', $join, $filters, $page, $perPage);
    }

    /**
     * Full activity timeline for one user (work tracking).
     */
    public static function userWorkTimeline(int $userId, int $limit = 200): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM `activities`
             WHERE `user_id` = :uid
             ORDER BY `id` DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function recentActivities(int $limit = 10): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT a.*, u.name AS user_name, u.email AS user_email
             FROM `activities` a
             LEFT JOIN `users` u ON u.id = a.user_id
             ORDER BY a.id DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Lowest id within the N most recent rows of a table. Used to scope
     * dashboard widgets to "last N rows".
     */
    public static function latestMinId(string $table, int $rowLimit): int
    {
        $stmt = Database::pdo()->prepare(
            "SELECT IFNULL(MIN(id), 0) FROM (SELECT `id` FROM `{$table}` ORDER BY `id` DESC LIMIT :lim) t"
        );
        $stmt->bindValue(':lim', (int)$rowLimit, \PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public static function recentLogins(int $limit = 8): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT h.*, u.name AS user_name
             FROM `login_history` h
             LEFT JOIN `users` u ON u.id = h.user_id
             ORDER BY h.id DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}