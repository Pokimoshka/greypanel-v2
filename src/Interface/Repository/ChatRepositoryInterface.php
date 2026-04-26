<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

interface ChatRepositoryInterface
{
    public function findMessages(int $sinceId = 0, int $limit = 50): array;
    public function addMessage(int $userId, string $message): int;
    public function deleteMessage(int $id): bool;
}
