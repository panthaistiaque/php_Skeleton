<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ApiToken extends Model
{
    protected string $table = 'api_tokens';
    protected array $fillable = ['user_id', 'name', 'token_hash', 'abilities', 'last_used_at', 'expires_at', 'revoked_at'];
    protected bool $timestamps = false;

    /**
     * Generate a new random token; returns [plaintext, id].
     */
    public static function createFor(int $userId, string $name, ?string $expiresAt = null): array
    {
        $plain = 'sk_' . bin2hex(random_bytes(32));
        $id = self::insertGetId([
            'user_id'    => $userId,
            'name'       => $name,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => $expiresAt,
        ]);

        return [$plain, (int)$id];
    }
}