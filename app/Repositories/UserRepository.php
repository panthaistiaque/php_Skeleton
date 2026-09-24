<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Read-side queries for the user list + aggregation.
 */
final class UserRepository
{
    /**
     * Paginated user listing with role/department/designation joins.
     *
     * @return array{items:array,total:int,page:int,per_page:int,last_page:int}
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = Database::pdo();

        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(u.name LIKE :search1 OR u.email LIKE :search2)';
            $params[':search1'] = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 'u.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['role_id'])) {
            $where[] = 'EXISTS (SELECT 1 FROM `user_role` x WHERE x.user_id = u.id AND x.role_id = :role_id)';
            $params[':role_id'] = (int)$filters['role_id'];
        }
        if (!empty($filters['department_id'])) {
            $where[] = 'u.department_id = :dept';
            $params[':dept'] = (int)$filters['department_id'];
        }

        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `users` u{$whereSql}");
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        $lastPage = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT u.*,
                    COALESCE(GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', '), '') AS roles
                FROM `users` u
                LEFT JOIN `user_role` ur ON ur.user_id = u.id
                LEFT JOIN `roles` r ON r.id = ur.role_id
                {$whereSql}
                GROUP BY u.id
                ORDER BY u.created_at DESC, u.id DESC
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

    public static function dashboardStats(): array
    {
        $pdo = Database::pdo();

        $users = (int)$pdo->query('SELECT COUNT(*) FROM `users`')->fetchColumn();
        $active = (int)$pdo->query('SELECT COUNT(*) FROM `users` WHERE `status` = "active"')->fetchColumn();
        $pending = (int)$pdo->query('SELECT COUNT(*) FROM `users` WHERE `status` = "pending"')->fetchColumn();
        $roles = (int)$pdo->query('SELECT COUNT(*) FROM `roles` WHERE `status` = "active"')->fetchColumn();
        $today = (int)$pdo->query('SELECT COUNT(*) FROM `login_history` WHERE `status`="success" AND DATE(`created_at`) = CURDATE()')->fetchColumn();
        $newUsersThisMonth = (int)$pdo->query('SELECT COUNT(*) FROM `users` WHERE `created_at` >= DATE_FORMAT(CURDATE(), "%Y-%m-01")')->fetchColumn();

        return [
            'total_users'       => $users,
            'active_users'      => $active,
            'pending_users'     => $pending,
            'inactive_users'    => $users - $active - $pending,
            'active_roles'      => $roles,
            'logins_today'      => $today,
            'new_users_month'   => $newUsersThisMonth,
        ];
    }

    /**
     * Daily logins and new registrations for the last N days (chart data).
     */
    public static function loginTrend(int $days = 14): array
    {
        $pdo = Database::pdo();
        $labels = [];
        $logins = [];
        $registrations = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('d M', strtotime($day));
            $logins[] = (int)$pdo->query("SELECT COUNT(*) FROM `login_history` WHERE `status` = 'success' AND DATE(`created_at`) = '{$day}'")->fetchColumn();
            $registrations[] = (int)$pdo->query("SELECT COUNT(*) FROM `users` WHERE DATE(`created_at`) = '{$day}'")->fetchColumn();
        }

        return [
            'labels'        => $labels,
            'logins'        => $logins,
            'registrations' => $registrations,
        ];
    }
}