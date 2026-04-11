<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

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
        foreach ($users as &$user) {
            $user['last_activity'] = $this->timeAgo($user['last_activity']);
        }
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

    private function timeAgo(int $timestamp): string
    {
        $diff = time() - $timestamp;
        if ($diff < 60) return 'только что';
        if ($diff < 3600) return round($diff / 60) . ' мин назад';
        if ($diff < 86400) return round($diff / 3600) . ' ч назад';
        return date('d.m.Y H:i', $timestamp);
    }
}