<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class EmailVerification extends Model
{
    protected string $table = 'email_verifications';
    protected array $fillable = ['user_id', 'token_hash', 'expires_at', 'used_at'];
    protected bool $timestamps = false;

    public static function validFor(int $userId, string $tokenHash): ?array
    {
        return self::firstWhere([
            'user_id'    => $userId,
            'token_hash' => $tokenHash,
            'used_at'    => null,
        ]);
    }

    public static function markUsed(int $id): void
    {
        self::updateById($id, ['used_at' => date('Y-m-d H:i:s')]);
    }
}