<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

interface LogRepositoryInterface
{
    public function add(int $userId, string $action, ?string $details = null): void;
    public function findPaginated(int $page, int $perPage = 30): array;
    public function count(): int;
    public function deleteOlderThan(int $days): int;
}
