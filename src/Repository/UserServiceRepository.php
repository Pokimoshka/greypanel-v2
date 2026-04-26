<?php

declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;
use GreyPanel\Model\UserService;

class UserServiceRepository
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('user_services');
    }

    public function findById(int $id): ?UserService
    {
        $row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        return $row ? new UserService($row) : null;
    }

    public function findByUser(int $userId, bool $onlyValid = true): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        if ($onlyValid) {
            $sql .= " AND expires_at > " . time();
        }
        $sql .= " ORDER BY expires_at DESC";
        $rows = $this->db->fetchAll($sql, [$userId]);
        return array_map(fn ($row) => new UserService($row), $rows);
    }

    public function findActiveByService(int $userId, int $serviceId): ?UserService
    {
        $now = time();
        $row = $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE user_id = ? AND service_id = ? AND expires_at > ? ORDER BY expires_at DESC LIMIT 1",
            [$userId, $serviceId, $now]
        );
        return $row ? new UserService($row) : null;
    }

    public function create(UserService $userService): int
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (user_id, service_id, tariff_id, expires_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)",
            [$userService->getUserId(), $userService->getServiceId(), $userService->getTariffId(), $userService->getExpiresAt(), $now, $now]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(UserService $userService): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET expires_at = ?, updated_at = ? WHERE id = ?",
            [$userService->getExpiresAt(), time(), $userService->getId()]
        );
    }

    public function deleteExpired(): int
    {
        $now = time();
        $stmt = $this->db->query("DELETE FROM {$this->table} WHERE expires_at <= ?", [$now]);
        return $stmt->rowCount();
    }
}
