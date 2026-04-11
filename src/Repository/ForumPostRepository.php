<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class ForumPostRepository implements ForumPostRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('forum_posts');
    }

    public function findByThreadId(int $threadId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE thread_id = ? ORDER BY created_at ASC LIMIT ? OFFSET ?",
            [$threadId, $perPage, $offset]
        );
    }

    public function countByThreadId(int $threadId): int
    {
        $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM {$this->table} WHERE thread_id = ?", [$threadId]);
        return (int)($row['cnt'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function create(int $threadId, int $userId, string $content): int
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (thread_id, user_id, content, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
            [$threadId, $userId, $content, $now, $now]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(int $id, string $content): void
    {
        $now = time();
        $this->db->query(
            "UPDATE {$this->table} SET content = ?, updated_at = ? WHERE id = ?",
            [$content, $now, $id]
        );
    }

    public function delete(int $id): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function findLastByThreadId(int $threadId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE thread_id = ? ORDER BY created_at DESC LIMIT 1",
            [$threadId]
        );
    }
}