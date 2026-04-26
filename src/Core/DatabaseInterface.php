<?php

declare(strict_types=1);

namespace GreyPanel\Core;

use PDO;

interface DatabaseInterface
{
    public function getPdo(): PDO;
    public function table(string $name): string;
    public function query(string $sql, array $params = []): \PDOStatement;
    public function fetchOne(string $sql, array $params = []): ?array;
    public function fetchAll(string $sql, array $params = []): array;
    public function insert(string $table, array $data): int;
    public function update(string $table, array $data, array $where): int;
    public function delete(string $table, array $where): int;
}
