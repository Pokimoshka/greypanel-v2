<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

interface ForumThreadRepositoryInterface
{
    public function findByForumId(int $forumId, int $page, int $perPage): array;
    public function countByForumId(int $forumId): int;
    public function findById(int $id): ?array;
    public function create(int $forumId, int $userId, string $title, string $content): int;
    public function update(int $id, string $title, string $content): void;
    public function updateStats(int $threadId, int $replies, int $lastPostAt): void;
    public function incrementViews(int $threadId): void;
    public function deleteSoft(int $threadId): void;
    public function countAll(): int;
    public function findLast(int $limit): array;
}