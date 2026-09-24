<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ActivityLog extends Model
{
    protected string $table = 'activities';
    protected array $fillable = ['user_id', 'action', 'module', 'description', 'method', 'url', 'ip_address', 'user_agent', 'context'];
    protected bool $timestamps = false;

    public static function createEntry(array $data): void
    {
        self::insertGetId($data);
    }
}