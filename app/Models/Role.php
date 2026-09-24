<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Role extends Model
{
    protected string $table = 'roles';
    protected array $fillable = ['name', 'slug', 'description', 'is_system', 'status'];

    public static function permissionIds(int $roleId): array
    {
        $pdo = (new self())->db();
        $stmt = $pdo->prepare('SELECT `permission_id` FROM `role_permission` WHERE `role_id` = :id');
        $stmt->execute([':id' => $roleId]);

        return array_map('intval', array_column($stmt->fetchAll(), 'permission_id'));
    }

    public static function assignPermissions(int $roleId, array $permissionIds): void
    {
        $pdo = (new self())->db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE FROM `role_permission` WHERE `role_id` = :id');
            $stmt->execute([':id' => $roleId]);

            $insert = $pdo->prepare('INSERT INTO `role_permission` (`role_id`, `permission_id`) VALUES (:role, :perm)');
            foreach (array_unique(array_map('intval', $permissionIds)) as $permissionId) {
                $insert->execute([':role' => $roleId, ':perm' => $permissionId]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function isAssignedToUsers(int $roleId): bool
    {
        $stmt = (new self())->db()->prepare('SELECT COUNT(*) FROM `user_role` WHERE `role_id` = :id');
        $stmt->execute([':id' => $roleId]);

        return (int)$stmt->fetchColumn() > 0;
    }
}