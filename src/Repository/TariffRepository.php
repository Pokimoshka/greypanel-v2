<?php

declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;
use GreyPanel\Model\Tariff;

class TariffRepository
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('tariffs');
    }

    public function findById(int $id): ?Tariff
    {
        $row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        return $row ? new Tariff($row) : null;
    }

    public function findByServiceId(int $serviceId, bool $onlyActive = true): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE service_id = ?" . ($onlyActive ? " AND is_active = 1" : "") . " ORDER BY sort_order ASC";
        $rows = $this->db->fetchAll($sql, [$serviceId]);
        return array_map(fn ($row) => new Tariff($row), $rows);
    }

    public function create(Tariff $tariff): int
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (service_id, duration_days, price, is_active, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$tariff->getServiceId(), $tariff->getDurationDays(), $tariff->getPrice(), (int)$tariff->isActive(), $tariff->getSortOrder(), $now, $now]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(Tariff $tariff): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET duration_days = ?, price = ?, is_active = ?, sort_order = ?, updated_at = ? WHERE id = ?",
            [$tariff->getDurationDays(), $tariff->getPrice(), (int)$tariff->isActive(), $tariff->getSortOrder(), time(), $tariff->getId()]
        );
    }

    public function delete(int $id): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}
