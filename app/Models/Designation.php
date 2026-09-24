<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Designation extends Model
{
    protected string $table = 'designations';
    protected array $fillable = ['name', 'department_id', 'description', 'status'];

    public static function active(): array
    {
        return self::where(['status' => 'active'], ['name' => 'ASC']);
    }

    public static function activeForDepartment(int $departmentId): array
    {
        return self::where(['status' => 'active', 'department_id' => $departmentId], ['name' => 'ASC']);
    }

    public static function hasUsers(int $designationId): bool
    {
        $stmt = (new self())->db()->prepare('SELECT COUNT(*) FROM `users` WHERE `designation_id` = :id');
        $stmt->execute([':id' => $designationId]);

        return (int)$stmt->fetchColumn() > 0;
    }
}