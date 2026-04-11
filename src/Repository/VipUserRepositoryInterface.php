<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

interface VipUserRepositoryInterface
{
    public function countActive(): int;
    public function findByUserId(int $userId): array;
    public function findActiveByUserAndServer(int $userId, int $serverId): ?array;
    public function create(int $userId, int $serverId, int $privilegeId, int $expiredAt): int;
    public function updateExpired(int $id, int $newExpiredAt): void;
    public function deleteExpired(): int;
}