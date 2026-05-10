<?php

declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;
use GreyPanel\Interface\Repository\OnlineRepositoryInterface;

final class OnlineRepository implements OnlineRepositoryInterface
{
    private Database $db;
    private string $table;
    private string $usersTable;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('online');
        $this->usersTable = $db->table('users');
    }

    public function findOnlineUsers(): array
    {
        $now = time();
        $expire = $now - 300;
        $users = $this->db->fetchAll(
            "SELECT o.user_id, o.last_activity, u.username, u.avatar
             FROM {$this->table} o
             LEFT JOIN {$this->usersTable} u ON o.user_id = u.id
             WHERE o.last_activity > ?
             ORDER BY o.last_activity DESC",
            [$expire]
        );

        return $users;
    }

    public function updateActivity(int $userId): void
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (user_id, last_activity) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE last_activity = ?",
            [$userId, $now, $now]
        );
    }

    public function deleteExpired(int $seconds = 300): int
    {
        $expire = time() - $seconds;
        $stmt = $this->db->query("DELETE FROM {$this->table} WHERE last_activity < ?", [$expire]);
        return $stmt->rowCount();
    }
}
