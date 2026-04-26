<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

interface ForumReadRepositoryInterface
{
    public function markAsRead(int $userId, int $threadId): void;
    public function findLastRead(int $userId, int $threadId): ?int;
}
