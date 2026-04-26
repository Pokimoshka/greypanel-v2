<?php

declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;
use GreyPanel\Interface\Repository\MoneyLogRepositoryInterface;

final class MoneyLogRepository implements MoneyLogRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('money_logs');
    }

    public function findByUserId(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY id DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public function findPaginatedByUserId(int $userId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY id DESC LIMIT ? OFFSET ?",
            [$userId, $perPage, $offset]
        );
    }

    public function countByUserId(int $userId): int
    {
        $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM {$this->table} WHERE user_id = ?", [$userId]);
        return (int)($row['cnt'] ?? 0);
    }

    public function getTotalRecharge(int $userId): int
    {
        $row = $this->db->fetchOne(
            "SELECT SUM(amount) as total FROM {$this->table} WHERE user_id = ? AND type = 0",
            [$userId]
        );
        return (int)($row['total'] ?? 0);
    }

    public function add(int $userId, int $amount, string $title, int $type = 0): void
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (user_id, type, amount, title, created_at) VALUES (?, ?, ?, ?, ?)",
            [$userId, $type, $amount, $title, $now]
        );
    }
}
