<?php

declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;
use GreyPanel\Model\Service;

class ServiceRepository
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('services');
    }

    public function findById(int $id): ?Service
    {
        $row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        return $row ? new Service($row) : null;
    }

    public function findAll(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM {$this->table} ORDER BY sort_order ASC");
        return array_map(fn ($row) => new Service($row), $rows);
    }

    public function findActive(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order ASC");
        return array_map(fn ($row) => new Service($row), $rows);
    }

    public function create(Service $service): int
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (name, description, rights, is_active, sort_order, group_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$service->getName(), $service->getDescription(), $service->getRights(), (int)$service->isActive(), $service->getSortOrder(), $service->getGroupId(), $now, $now]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(Service $service): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET name = ?, description = ?, rights = ?, is_active = ?, sort_order = ?, group_id = ?, updated_at = ? WHERE id = ?",
            [$service->getName(), $service->getDescription(), $service->getRights(), (int)$service->isActive(), $service->getSortOrder(), $service->getGroupId(), time(), $service->getId()]
        );
    }

    public function delete(int $id): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function duplicate(int $id): ?Service
    {
        $original = $this->findById($id);
        if (!$original) {
            return null;
        }
        $copy = new Service();
        $copy->setName($original->getName() . ' (копия)')
             ->setDescription($original->getDescription())
             ->setRights($original->getRights())
             ->setIsActive(false)
             ->setSortOrder($original->getSortOrder() + 1);
        $newId = $this->create($copy);
        return $this->findById($newId);
    }
}
