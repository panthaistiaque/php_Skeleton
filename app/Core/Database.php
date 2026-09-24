<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Single PDO connection (persistent within the request).
 * All queries in the application use prepared statements.
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $db = Config::get('database');

            $driver = strtolower($db['driver'] ?? 'mysql');
            $host = $db['host'] ?? '127.0.0.1';
            $port = $db['port'] ?? '3306';
            $name = $db['database'] ?? '';
            $charset = $db['charset'] ?? 'utf8mb4';

            $dsn = match ($driver) {
                'pgsql' => "pgsql:host={$host};port={$port};dbname={$name}",
                'sqlite' => "sqlite:" . ($db['path'] ?? __DIR__ . '/../../database/app.sqlite'),
                default => "mysql:host={$host};port={$port};dbname={$name};charset={$charset}",
            };

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ];

            if ($driver === 'mysql') {
                // Prevent MySQL from silently casting `int` strings into ints,
                // keeps fetched values predictable.
                $options[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
            }

            try {
                self::$instance = new PDO($dsn, (string)($db['username'] ?? 'root'), (string)($db['password'] ?? ''), $options);
            } catch (PDOException $e) {
                throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 500, $e);
            }
        }

        return self::$instance;
    }

    /**
     * Convenience alias.
     */
    public static function pdo(): PDO
    {
        return self::connection();
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connection();

        try {
            $pdo->beginTransaction();
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function lastInsertId(): string
    {
        return (string)self::connection()->lastInsertId();
    }

    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}