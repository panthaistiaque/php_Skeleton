<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Query helpers for the reporting module (CSV exports).
 */
final class ReportRepository
{
    public static function users(array $filters = []): array
    {
        $pdo = Database::pdo();
        $where = [];
        $params = [];

        if (!empty($filters['from'])) {
            $where[] = 'u.created_at >= :from';
            $params[':from'] = $filters['from'] . ' 00:00:00';
        }
        if (!empty($filters['to'])) {
            $where[] = 'u.created_at <= :to';
            $params[':to'] = $filters['to'] . ' 23:59:59';
        }
        if (!empty($filters['status'])) {
            $where[] = 'u.status = :status';
            $params[':status'] = $filters['status'];
        }

        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT u.id, u.name, u.email, u.status, IFNULL(u.last_login_at,'') AS last_login_at, u.created_at,
                       COALESCE(GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', '), '') AS roles
                FROM `users` u
                LEFT JOIN `user_role` ur ON ur.user_id = u.id
                LEFT JOIN `roles` r ON r.id = ur.role_id
                {$whereSql}
                GROUP BY u.id
                ORDER BY u.id ASC";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function logins(array $filters = []): array
    {
        $pdo = Database::pdo();
        $where = [];
        $params = [];

        if (!empty($filters['from'])) {
            $where[] = 'x.created_at >= :from';
            $params[':from'] = $filters['from'] . ' 00:00:00';
        }
        if (!empty($filters['to'])) {
            $where[] = 'x.created_at <= :to';
            $params[':to'] = $filters['to'] . ' 23:59:59';
        }
        if (!empty($filters['status'])) {
            $where[] = 'x.status = :status';
            $params[':status'] = $filters['status'];
        }

        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT x.id, IFNULL(u.name,'') AS user_name, IFNULL(x.email,'') AS email,
                       x.ip_address, x.device_type, x.browser, x.platform, x.status, x.reason, x.created_at
                FROM `login_history` x
                LEFT JOIN `users` u ON u.id = x.user_id
                {$whereSql}
                ORDER BY x.id DESC";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function activities(array $filters = []): array
    {
        $pdo = Database::pdo();
        $where = [];
        $params = [];

        if (!empty($filters['from'])) {
            $where[] = 'x.created_at >= :from';
            $params[':from'] = $filters['from'] . ' 00:00:00';
        }
        if (!empty($filters['to'])) {
            $where[] = 'x.created_at <= :to';
            $params[':to'] = $filters['to'] . ' 23:59:59';
        }
        if (!empty($filters['action'])) {
            $where[] = 'x.action = :action';
            $params[':action'] = $filters['action'];
        }

        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT x.id, IFNULL(u.name,'') AS user_name, x.action, x.module,
                       IFNULL(x.description,'') AS description, x.method, x.url, x.ip_address, x.created_at
                FROM `activities` x
                LEFT JOIN `users` u ON u.id = x.user_id
                {$whereSql}
                ORDER BY x.id DESC
                LIMIT 10000";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }
}