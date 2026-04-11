<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface ChatServiceInterface
{
    public function getMessages(int $sinceId = 0, int $limit = 50): array;
    public function sendMessage(int $userId, string $message): array;
    public function deleteMessage(int $id): bool;
}