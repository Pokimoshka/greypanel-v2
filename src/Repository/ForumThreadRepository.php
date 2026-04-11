<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class ForumThreadRepository implements ForumThreadRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('forum_threads');
    }

    public function findByForumId(int $forumId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE forum_id = ? AND is_deleted = 0 ORDER BY is_sticky DESC, last_post_at DESC LIMIT ? OFFSET ?",
            [$forumId, $perPage, $offset]
        );
    }

    public function countByForumId(int $forumId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE forum_id = ? AND is_deleted = 0",
            [$forumId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ? AND is_deleted = 0",
            [$id]
        );
    }

    public function create(int $forumId, int $userId, string $title, string $content): int
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (forum_id, user_id, title, content, created_at, updated_at, last_post_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$forumId, $userId, $title, $content, $now, $now, $now]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(int $id, string $title, string $content): void
    {
        $now = time();
        $this->db->query(
            "UPDATE {$this->table} SET title = ?, content = ?, updated_at = ? WHERE id = ?",
            [$title, $content, $now, $id]
        );
    }

    public function updateStats(int $threadId, int $replies, int $lastPostAt): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET replies = ?, last_post_at = ? WHERE id = ?",
            [$replies, $lastPostAt, $threadId]
        );
    }

    public function incrementViews(int $threadId): void
    {
        $this->db->query("UPDATE {$this->table} SET views = views + 1 WHERE id = ?", [$threadId]);
    }

    public function deleteSoft(int $threadId): void
    {
        $this->db->query("UPDATE {$this->table} SET is_deleted = 1 WHERE id = ?", [$threadId]);
    }

    public function countAll(): int
    {
        $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM {$this->table} WHERE is_deleted = 0");
        return (int)($row['cnt'] ?? 0);
    }

    public function findLast(int $limit): array
    {
        $prefix = $this->db->getPrefix();
        return $this->db->fetchAll(
            "SELECT t.*, u.username as author_name 
             FROM {$this->table} t
             LEFT JOIN {$prefix}users u ON t.user_id = u.id
             WHERE t.is_deleted = 0
             ORDER BY t.created_at DESC 
             LIMIT ?",
            [$limit]
        );
    }
}