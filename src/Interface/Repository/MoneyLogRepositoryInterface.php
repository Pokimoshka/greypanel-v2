<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

interface MoneyLogRepositoryInterface
{
    public function findByUserId(int $userId, int $limit = 20): array;
    public function findPaginatedByUserId(int $userId, int $page, int $perPage): array;
    public function countByUserId(int $userId): int;
    public function getTotalRecharge(int $userId): int;
    public function add(int $userId, int $amount, string $title, int $type = 0): void;
}
