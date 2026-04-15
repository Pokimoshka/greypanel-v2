<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface BanServiceInterface
{
    public function getBans(int $page, int $perPage): array;
    public function countBans(): int;
    public function searchBans(string $query): array;
    public function getBanById(int $id): ?array;
    public function deleteBan(int $id): bool;
    public function isActive(): bool;
}