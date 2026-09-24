<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Permission extends Model
{
    protected string $table = 'permissions';
    protected array $fillable = ['name', 'slug', 'module', 'description'];
    protected bool $timestamps = false;

    public static function groupedByModule(): array
    {
        $rows = self::all(['module' => 'ASC', 'id' => 'ASC']);
        $grouped = [];
        foreach ($rows as $permission) {
            $grouped[$permission['module']][] = $permission;
        }

        return $grouped;
    }

    public static function slugsForUser(int $userId): array
    {
        $pdo = (new self())->db();
        $stmt = $pdo->prepare(
            'SELECT DISTINCT p.slug
             FROM `permissions` p
             JOIN `role_permission` rp ON rp.permission_id = p.id
             JOIN `user_role` ur ON ur.role_id = rp.role_id
             JOIN `roles` r ON r.id = ur.role_id
             WHERE ur.user_id = :uid AND r.status = "active"'
        );
        $stmt->execute([':uid' => $userId]);

        return array_column($stmt->fetchAll(), 'slug');
    }
}