<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class VipUserRepository implements VipUserRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('vip_users');
    }

    public function countActive(): int
    {
        $now = time();
        $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM {$this->table} WHERE expired_at > ?", [$now]);
        return (int)($row['cnt'] ?? 0);
    }

    public function findByUserId(int $userId): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY expired_at DESC", [$userId]);
    }

    public function findActiveByUserAndServer(int $userId, int $serverId): ?array
    {
        $now = time();
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE user_id = ? AND server_id = ? AND expired_at > ? LIMIT 1",
            [$userId, $serverId, $now]
        );
    }

    public function create(int $userId, int $serverId, int $privilegeId, int $expiredAt): int
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (user_id, server_id, privilege_id, created_at, expired_at) VALUES (?, ?, ?, ?, ?)",
            [$userId, $serverId, $privilegeId, $now, $expiredAt]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function updateExpired(int $id, int $newExpiredAt): void
    {
        $this->db->query("UPDATE {$this->table} SET expired_at = ? WHERE id = ?", [$newExpiredAt, $id]);
    }

    public function deleteExpired(): int
    {
        $now = time();
        $stmt = $this->db->query("DELETE FROM {$this->table} WHERE expired_at <= ?", [$now]);
        return $stmt->rowCount();
    }
}