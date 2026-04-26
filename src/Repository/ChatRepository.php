<?php

declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;
use GreyPanel\Interface\Repository\ChatRepositoryInterface;

final class ChatRepository implements ChatRepositoryInterface
{
    private Database $db;
    private string $table;
    private string $usersTable;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('chat_messages');
        $this->usersTable = $db->table('users');
    }

    public function findMessages(int $sinceId = 0, int $limit = 50): array
    {
        $sql = "SELECT m.id, m.user_id, m.message, m.created_at,
                       u.username, u.avatar
                FROM {$this->table} m
                JOIN {$this->usersTable} u ON m.user_id = u.id
                WHERE m.id > ?
                ORDER BY m.id DESC
                LIMIT ?";
        return $this->db->fetchAll($sql, [$sinceId, $limit]);
    }

    public function addMessage(int $userId, string $message): int
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (user_id, message, created_at) VALUES (?, ?, ?)",
            [$userId, $message, $now]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function deleteMessage(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
}
