<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

interface PaymentRepositoryInterface
{
    public function add(int $userId, string $system, int $amount, string $externalId, int $status = 0): int;
    public function findByExternalId(string $externalId): ?array;
    public function updateStatus(string $externalId, int $status): void;
}
