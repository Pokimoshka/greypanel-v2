<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

interface OnlineRepositoryInterface
{
    public function findOnlineUsers(): array;
    public function updateActivity(int $userId): void;
}
