<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Core\Database;

final class ModuleService implements ModuleServiceInterface
{
    private Database $db;
    private string $table;
    private array $cache = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('modules');
    }

    public function isEnabled(string $name): bool
    {
        if (!isset($this->cache[$name])) {
            $row = $this->db->fetchOne("SELECT enabled FROM {$this->table} WHERE name = ?", [$name]);
            $this->cache[$name] = $row ? (bool)$row['enabled'] : false;
        }
        return $this->cache[$name];
    }

    public function setEnabled(string $name, bool $enabled): void
    {
        $this->db->query(
            "INSERT INTO {$this->table} (name, enabled) VALUES (?, ?) ON DUPLICATE KEY UPDATE enabled = ?",
            [$name, (int)$enabled, (int)$enabled]
        );
        $this->cache[$name] = $enabled;
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }
}