<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Upload extends Model
{
    protected string $table = 'uploads';
    protected array $fillable = ['user_id', 'original_name', 'stored_name', 'path', 'mime_type', 'extension', 'category', 'size', 'disk', 'status'];
    protected bool $timestamps = false;

    public static function byUser(int $userId, int $limit = 100): array
    {
        return self::where(['user_id' => $userId], ['id' => 'DESC']);
    }
}