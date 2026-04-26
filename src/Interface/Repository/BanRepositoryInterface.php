<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

interface BanRepositoryInterface
{
    public function findPaginated(int $page, int $perPage): array;
    public function count(): int;
    public function search(string $query): array;
    public function findById(int $id): ?array;
    public function deleteBan(int $bid): bool;
}
