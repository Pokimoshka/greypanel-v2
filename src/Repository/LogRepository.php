<?php

declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;
use GreyPanel\Interface\Repository\LogRepositoryInterface;

final class LogRepository implements LogRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('logs');
    }

    public function add(int $userId, string $action, ?string $details = null): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (user_id, action, details, ip, created_at) VALUES (?, ?, ?, ?, ?)",
            [$userId, $action, $details, $ip, $now]
        );
    }

    public function findPaginated(int $page, int $perPage = 30): array
    {
        $offset = ($page - 1) * $perPage;
        $prefix = $this->db->getPrefix();
        $usersTable = $prefix . 'users';
        return $this->db->fetchAll(
            "SELECT l.*, u.username FROM {$this->table} l 
             LEFT JOIN {$usersTable} u ON l.user_id = u.id 
             ORDER BY l.id DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }

    public function count(): int
    {
        $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM {$this->table}");
        return (int)($row['cnt'] ?? 0);
    }

    public function deleteOlderThan(int $days): int
    {
        $timestamp = time() - ($days * 86400);
        $stmt = $this->db->query("DELETE FROM {$this->table} WHERE created_at < ?", [$timestamp]);
        return $stmt->rowCount();
    }
}
