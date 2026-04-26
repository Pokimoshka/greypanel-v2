<?php

declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

class ServiceServerRepository
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('service_servers');
    }

    public function getServerIdsForService(int $serviceId): array
    {
        $rows = $this->db->fetchAll("SELECT server_id FROM {$this->table} WHERE service_id = ?", [$serviceId]);
        return array_column($rows, 'server_id');
    }

    public function setServersForService(int $serviceId, array $serverIds): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE service_id = ?", [$serviceId]);
        foreach ($serverIds as $serverId) {
            $this->db->query("INSERT INTO {$this->table} (service_id, server_id) VALUES (?, ?)", [$serviceId, $serverId]);
        }
    }
}
