<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

interface BanServiceInterface
{
    public function getBans(int $page, int $perPage, ?string $search = null, ?int $statusFilter = null): array;
    public function countBans(?string $search = null, ?int $statusFilter = null): int;
    public function searchBans(string $query): array;
    public function getBanById(int $id): ?array;
    public function deleteBan(int $id): bool;
    public function isActive(): bool;
    public function getPaginatedBans(int $page, int $perPage, ?string $search = null): array;
}
