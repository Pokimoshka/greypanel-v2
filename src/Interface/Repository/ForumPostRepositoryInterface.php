<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

interface ForumPostRepositoryInterface
{
    public function findByThreadId(int $threadId, int $page, int $perPage): array;
    public function countByThreadId(int $threadId): int;
    public function findById(int $id): ?array;
    public function create(int $threadId, int $userId, string $content): int;
    public function update(int $id, string $content): void;
    public function delete(int $id): void;
    public function findLastByThreadId(int $threadId): ?array;
}
