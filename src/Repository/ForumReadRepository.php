<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class ForumReadRepository implements ForumReadRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('forum_read');
    }

    public function markAsRead(int $userId, int $threadId): void
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (user_id, thread_id, last_read_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE last_read_at = ?",
            [$userId, $threadId, $now, $now]
        );
    }

    public function findLastRead(int $userId, int $threadId): ?int
    {
        $row = $this->db->fetchOne(
            "SELECT last_read_at FROM {$this->table} WHERE user_id = ? AND thread_id = ?",
            [$userId, $threadId]
        );
        return $row ? (int)$row['last_read_at'] : null;
    }
}