<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class ForumLikeRepository implements ForumLikeRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('forum_likes');
    }

    public function hasLiked(int $userId, string $targetType, int $targetId): bool
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE user_id = ? AND target_type = ? AND target_id = ?",
            [$userId, $targetType, $targetId]
        );
        return ($row['cnt'] ?? 0) > 0;
    }

    public function addLike(int $userId, string $targetType, int $targetId): void
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (user_id, target_type, target_id, created_at) VALUES (?, ?, ?, ?)",
            [$userId, $targetType, $targetId, $now]
        );
    }

    public function removeLike(int $userId, string $targetType, int $targetId): void
    {
        $this->db->query(
            "DELETE FROM {$this->table} WHERE user_id = ? AND target_type = ? AND target_id = ?",
            [$userId, $targetType, $targetId]
        );
    }

    public function countLikes(string $targetType, int $targetId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE target_type = ? AND target_id = ?",
            [$targetType, $targetId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    public function findLikedUsers(string $targetType, int $targetId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT user_id FROM {$this->table} WHERE target_type = ? AND target_id = ? ORDER BY created_at DESC LIMIT ?",
            [$targetType, $targetId, $limit]
        );
    }
}