<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class LoginHistory extends Model
{
    protected string $table = 'login_history';
    protected array $fillable = ['user_id', 'email', 'ip_address', 'user_agent', 'device_type', 'browser', 'platform', 'status', 'reason'];
    protected bool $timestamps = false;

    public static function createEntry(array $data): void
    {
        self::insertGetId($data);
    }

    public static function failedCountFor(string $email, string $ip, int $withinMinutes): int
    {
        $model = new self();
        $stmt = $model->db()->prepare(
            'SELECT COUNT(*) FROM `login_history`
             WHERE `status` = "failed" AND `created_at` >= :since
             AND (`email` = :email OR `ip_address` = :ip)'
        );
        $since = date('Y-m-d H:i:s', strtotime("-{$withinMinutes} minutes"));
        $stmt->execute([':since' => $since, ':email' => $email, ':ip' => $ip]);

        return (int)$stmt->fetchColumn();
    }
}