<?php

declare(strict_types=1);

namespace GreyPanel\Core;

use PDO;
use PDOException;

final class Database implements DatabaseInterface
{
    private PDO $pdo;
    private string $prefix;

    public function __construct(array $config)
    {
        $host = $config['DB_HOST'] ?? 'localhost';
        $dbname = $config['DB_NAME'] ?? '';
        $user = $config['DB_USER'] ?? 'root';
        $pass = $config['DB_PASS'] ?? '';
        $charset = $config['DB_CHARSET'] ?? 'utf8mb4';
        $this->prefix = $config['DB_PREFIX'] ?? 'grey_';

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
    public function table(string $name): string
    {
        return $this->prefix . $name;
    }
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert(string $table, array $data): int
    {
        $full = $this->table($table);
        $cols = implode('`, `', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO `{$full}` (`{$cols}`) VALUES ({$placeholders})";
        $this->query($sql, $data);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, array $where): int
    {
        $full = $this->table($table);
        $set = [];
        foreach ($data as $k => $v) {
            $set[] = "`{$k}` = :{$k}";
        }
        $w = [];
        foreach ($where as $k => $v) {
            $w[] = "`{$k}` = :where_{$k}";
            $data["where_{$k}"] = $v;
        }
        $sql = "UPDATE `{$full}` SET " . implode(', ', $set) . " WHERE " . implode(' AND ', $w);
        $stmt = $this->query($sql, $data);
        return $stmt->rowCount();
    }

    public function delete(string $table, array $where): int
    {
        $full = $this->table($table);
        $w = [];
        foreach ($where as $k => $v) {
            $w[] = "`{$k}` = :{$k}";
        }
        $sql = "DELETE FROM `{$full}` WHERE " . implode(' AND ', $w);
        $stmt = $this->query($sql, $where);
        return $stmt->rowCount();
    }
}
