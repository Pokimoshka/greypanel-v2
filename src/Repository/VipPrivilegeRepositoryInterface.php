<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

interface VipPrivilegeRepositoryInterface
{
    public function findByServerId(int $serverId): array;
    public function findById(int $id): ?array;
    public function create(int $serverId, string $title, string $flags, int $pricePerDay): int;
    public function update(int $id, string $title, string $flags, int $pricePerDay): void;
    public function delete(int $id): void;
}