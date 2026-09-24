<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Base model implementing small, safe CRUD against its own table.
 * Every query uses prepared statements; table names are internal constants.
 *
 * Concrete subclasses declare $table (and optionally $fillable).
 */
abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $casts = [];
    protected array $searchable = [];
    protected bool $timestamps = true;

    use Concerns\BuildsQueries;

    public function __construct()
    {
        if ($this->table === '') {
            $this->table = $this->inferTableName();
        }
    }

    public static function tableName(): string
    {
        return (new static())->table;
    }

    protected function inferTableName(): string
    {
        $class = static::class;

        return strtolower((string)preg_replace('/(?<!^)[A-Z]/', '_$0', class_basename($class)));
    }

    public function db(): PDO
    {
        return Database::pdo();
    }

    public function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Filter an input array down to the model's fillable columns.
     */
    public function clean(array $data): array
    {
        if ($this->fillable === []) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function applyTimestamps(array $data, bool $creating): array
    {
        if (!$this->timestampsEnabled()) {
            return $data;
        }
        $now = $this->now();
        if ($creating && !isset($data['created_at'])) {
            $data['created_at'] = $now;
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = $now;
        }

        return $data;
    }

    protected function timestampsEnabled(): bool
    {
        return $this->timestamps;
    }
}