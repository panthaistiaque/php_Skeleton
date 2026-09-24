<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PasswordReset extends Model
{
    protected string $table = 'password_resets';
    protected array $fillable = ['email', 'token_hash', 'expires_at', 'used_at'];
    protected bool $timestamps = false;

    public static function validFor(string $email, string $tokenHash): ?array
    {
        return self::firstWhere([
            'email'      => $email,
            'token_hash' => $tokenHash,
            'used_at'    => null,
        ]);
    }

    public static function invalidateFor(string $email): void
    {
        $model = new self();
        $stmt = $model->db()->prepare('UPDATE `password_resets` SET `used_at` = :now WHERE `email` = :email AND `used_at` IS NULL');
        $stmt->execute([':email' => $email, ':now' => date('Y-m-d H:i:s')]);
    }

    public static function pruneExpired(): void
    {
        $model = new self();
        $stmt = $model->db()->prepare('DELETE FROM `password_resets` WHERE `expires_at` < :now OR `used_at` IS NOT NULL');
        $stmt->execute([':now' => date('Y-m-d H:i:s')]);
    }
}