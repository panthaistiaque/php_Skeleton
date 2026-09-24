<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class SecurityEvent extends Model
{
    protected string $table = 'security_events';
    protected array $fillable = ['user_id', 'event_type', 'severity', 'message', 'ip_address', 'user_agent', 'context'];
    protected bool $timestamps = false;

    public static function createEvent(array $data): void
    {
        self::insertGetId($data);
    }

    public static function countSince(string $since, string $severity = 'critical'): int
    {
        $model = new self();
        $stmt = $model->db()->prepare(
            'SELECT COUNT(*) FROM `security_events` WHERE `severity` = :sev AND `created_at` >= :since'
        );
        $stmt->execute([':sev' => $severity, ':since' => $since]);

        return (int)$stmt->fetchColumn();
    }
}