<?php

declare(strict_types=1);

namespace App\Core\Concerns;

use PDO;

/**
 * Prepared-statement query builders shared by all models.
 */
trait BuildsQueries
{
    public static function find(int|string $id): ?array
    {
        $model = new static();
        $stmt = $model->db()->prepare("SELECT * FROM `{$model->table}` WHERE `{$model->primaryKey}` = :id LIMIT 1");
        $stmt->bindValue(':id', (string)$id);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row === false ? null : $model->castRow($row);
    }

    /**
     * First row matching the where map (column => value).
     */
    public static function firstWhere(array $where): ?array
    {
        $model = new static();
        [$sql, $params] = $model->buildWhere($where);
        $stmt = $model->db()->prepare("SELECT * FROM `{$model->table}` WHERE {$sql} LIMIT 1");
        $model->bindParams($stmt, $params);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row === false ? null : $model->castRow($row);
    }

    public static function all(array $orderBy = []): array
    {
        $model = new static();
        $order = $model->buildOrder($orderBy);
        $stmt = $model->db()->query("SELECT * FROM `{$model->table}`{$order}");

        return array_map(fn($row) => $model->castRow($row), $stmt->fetchAll());
    }

    /**
     * Rows filtered by a where map plus optional ordering.
     */
    public static function where(array $where, array $orderBy = []): array
    {
        $model = new static();
        [$sql, $params] = $model->buildWhere($where);
        $order = $model->buildOrder($orderBy);
        $stmt = $model->db()->prepare("SELECT * FROM `{$model->table}` WHERE {$sql}{$order}");
        $model->bindParams($stmt, $params);
        $stmt->execute();

        return array_map(fn($row) => $model->castRow($row), $stmt->fetchAll());
    }

    public static function insertGetId(array $data): int
    {
        $model = new static();
        $data = $model->clean($data);
        $data = $model->applyTimestamps($data, true);

        $columns = array_keys($data);
        $placeholders = array_map(static fn(string $c) => ":{$c}", $columns);
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $model->table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $model->db()->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value, $model->bindType($value));
        }
        $stmt->execute();

        return (int)$model->db()->lastInsertId();
    }

    public static function updateById(int|string $id, array $data): bool
    {
        $model = new static();
        $data = $model->clean($data);
        $data = $model->applyTimestamps($data, false);

        if ($data === []) {
            return false;
        }

        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "`{$column}` = :{$column}";
        }
        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = :primary_id',
            $model->table,
            implode(', ', $set),
            $model->primaryKey
        );

        $stmt = $model->db()->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value, $model->bindType($value));
        }
        $stmt->bindValue(':primary_id', (string)$id);

        return $stmt->execute();
    }

    public static function deleteById(int|string $id): bool
    {
        $model = new static();
        $stmt = $model->db()->prepare("DELETE FROM `{$model->table}` WHERE `{$model->primaryKey}` = :id");
        $stmt->bindValue(':id', (string)$id);

        return $stmt->execute();
    }

    public static function countRows(?array $where = null): int
    {
        $model = new static();
        $whereSql = '';
        $params = [];
        if ($where !== null && $where !== []) {
            [$whereSql, $params] = $model->buildWhere($where);
            $whereSql = " WHERE {$whereSql}";
        }
        $stmt = $model->db()->prepare("SELECT COUNT(*) FROM `{$model->table}`{$whereSql}");
        $model->bindParams($stmt, $params);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Paginated listing with optional where map, ordering and search.
     *
     * @return array{items:array,total:int,page:int,per_page:int,last_page:int}
     */
    public static function paginate(array $where = [], array $orderBy = [], int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        $model = new static();
        $conditions = [];
        $params = [];

        if ($where !== []) {
            [$sql, $whereParams] = $model->buildWhere($where);
            $conditions[] = $sql;
            $params += $whereParams;
        }

        if ($search !== null && $search !== '' && $model->searchable !== []) {
            $like = [];
            foreach ($model->searchable as $index => $column) {
                $key = 'search_' . $index;
                $like[] = "`{$column}` LIKE :{$key}";
                $params[$key] = '%' . $search . '%';
            }
            $conditions[] = '(' . implode(' OR ', $like) . ')';
        }

        $whereSql = $conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $perPage = max(1, (int)$perPage);
        $page = max(1, (int)$page);

        $countStmt = $model->db()->prepare("SELECT COUNT(*) FROM `{$model->table}`{$whereSql}");
        $model->bindParams($countStmt, $params);
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        $lastPage = max(1, (int)ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $order = $model->buildOrder($orderBy);
        $stmt = $model->db()->prepare("SELECT * FROM `{$model->table}`{$whereSql}{$order} LIMIT :limit OFFSET :offset");
        $model->bindParams($stmt, $params);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = array_map(fn($row) => $model->castRow($row), $stmt->fetchAll());

        return [
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ];
    }

    protected function buildWhere(array $where): array
    {
        $sql = [];
        $params = [];
        foreach ($where as $column => $value) {
            if (is_array($value)) {
                // whereIn support: ['id' => [1,2,3]]
                $placeholders = [];
                foreach ($value as $i => $item) {
                    $key = "w_{$column}_{$i}";
                    $placeholders[] = ':' . $key;
                    $params[$key] = $item;
                }
                $sql[] = "`{$column}` IN (" . implode(', ', $placeholders) . ')';
            } else {
                $key = 'w_' . $column;
                if ($value === null) {
                    $sql[] = "`{$column}` IS NULL";
                } else {
                    $sql[] = "`{$column}` = :{$key}";
                    $params[$key] = $value;
                }
            }
        }

        return [implode(' AND ', $sql), $params];
    }

    protected function buildOrder(array $orderBy): string
    {
        if ($orderBy === []) {
            return '';
        }
        $parts = [];
        foreach ($orderBy as $column => $direction) {
            $direction = strtoupper((string)$direction) === 'DESC' ? 'DESC' : 'ASC';
            $parts[] = "`{$column}` {$direction}";
        }

        return ' ORDER BY ' . implode(', ', $parts);
    }

    protected function bindParams(\PDOStatement|false $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, $this->bindType($value));
        }
    }

    protected function bindType(mixed $value): int
    {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        }
        if (is_bool($value)) {
            return PDO::PARAM_BOOL;
        }
        if ($value === null) {
            return PDO::PARAM_NULL;
        }

        return PDO::PARAM_STR;
    }

    protected function castRow(array $row): array
    {
        foreach ($this->casts as $column => $type) {
            if (array_key_exists($column, $row)) {
                $row[$column] = $type === 'bool' ? (bool)$row[$column]
                    : ($type === 'int' ? (int)$row[$column]
                    : ($type === 'float' ? (float)$row[$column]
                    : ($type === 'json' ? (is_string($row[$column]) && $row[$column] !== '' ? json_decode($row[$column], true) : []) : $row[$column])));
            }
        }

        return $row;
    }
}