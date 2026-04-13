<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

interface MonitorServerRepositoryInterface
{
    public function findAll(): array;
    public function findEnabled(): array;
    public function findById(int $id): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): void;
    public function updateSettings(int $id, array $settings): void;
    public function updateStatus(int $id, int $status, string $cache, int $cacheTime): void;
    public function delete(int $id): void;
}