<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'name', 'email', 'password', 'status', 'email_verified_at', 'remember_token',
        'last_login_at', 'last_login_ip', 'failed_attempts', 'locked_until',
        'password_changed_at', 'avatar', 'department_id', 'designation_id', 'created_by',
    ];

    public static function findByEmail(string $email): ?array
    {
        return self::firstWhere(['email' => $email]);
    }

    public static function rolesFor(int $userId): array
    {
        $pdo = (new self())->db();
        $stmt = $pdo->prepare(
            'SELECT r.id, r.name, r.slug, r.is_system, r.status
             FROM `roles` r
             JOIN `user_role` ur ON ur.role_id = r.id
             WHERE ur.user_id = :uid AND r.status = "active"'
        );
        $stmt->execute([':uid' => $userId]);

        return $stmt->fetchAll();
    }

    public static function activeCount(): int
    {
        return self::countRows(['status' => 'active']);
    }

    public static function pendingCount(): int
    {
        return self::countRows(['status' => 'pending']);
    }

    public static function isSuperAdmin(int $userId): bool
    {
        foreach (self::rolesFor($userId) as $role) {
            if ((bool)$role['is_system']) {
                return true;
            }
        }

        return false;
    }
}