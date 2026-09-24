<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Department extends Model
{
    protected string $table = 'departments';
    protected array $fillable = ['name', 'code', 'description', 'status'];

    public static function active(): array
    {
        return self::where(['status' => 'active'], ['name' => 'ASC']);
    }

    public static function hasUsers(int $departmentId): bool
    {
        $stmt = (new self())->db()->prepare('SELECT COUNT(*) FROM `users` WHERE `department_id` = :id');
        $stmt->execute([':id' => $departmentId]);

        return (int)$stmt->fetchColumn() > 0;
    }
}