<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Setting extends Model
{
    protected string $table = 'settings';
    protected array $fillable = ['setting_key', 'setting_value', 'setting_type', 'group_name', 'is_encrypted', 'autoload'];
    protected bool $timestamps = false;

    public static function findByKey(string $key): ?array
    {
        return self::firstWhere(['setting_key' => $key]);
    }

    public static function allRows(): array
    {
        return self::all(['setting_key' => 'ASC']);
    }

    public static function upsert(array $data): void
    {
        $pdo = (new self())->db();
        $existing = self::findByKey($data['setting_key']);

        $payload = [
            'setting_value' => $data['setting_value'] ?? null,
            'setting_type'  => $data['setting_type'] ?? 'string',
            'group_name'    => $data['group_name'] ?? 'general',
            'is_encrypted'  => (int)($data['is_encrypted'] ?? 0),
            'autoload'      => (int)($data['autoload'] ?? 1),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        if ($existing !== null) {
            $set = [];
            foreach ($payload as $column => $value) {
                $set[] = "`{$column}` = :{$column}";
            }
            $stmt = $pdo->prepare('UPDATE `settings` SET ' . implode(', ', $set) . ' WHERE `setting_key` = :key');
            foreach ($payload as $column => $value) {
                $stmt->bindValue(':' . $column, is_bool($value) ? (int)$value : $value);
            }
            $stmt->bindValue(':key', $data['setting_key']);
            $stmt->execute();

            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `group_name`, `is_encrypted`, `autoload`, `updated_at`)
             VALUES (:key, :value, :type, :group, :encrypted, :autoload, :updated)'
        );
        $stmt->execute([
            ':key'       => $data['setting_key'],
            ':value'     => $payload['setting_value'],
            ':type'      => $payload['setting_type'],
            ':group'     => $payload['group_name'],
            ':encrypted' => $payload['is_encrypted'],
            ':autoload'  => $payload['autoload'],
            ':updated'   => date('Y-m-d H:i:s'),
        ]);
    }

    public static function remove(string $key): void
    {
        $pdo = (new self())->db();
        $stmt = $pdo->prepare('DELETE FROM `settings` WHERE `setting_key` = :key');
        $stmt->execute([':key' => $key]);
    }
}