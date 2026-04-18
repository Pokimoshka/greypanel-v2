<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface ForumServiceInterface
{
    public function getCategoriesWithForums(): array;
    public function getThreadsByForum(int $forumId, int $page, int $perPage = 20): array;
    public function getThreadsCount(int $forumId): int;
    public function getThread(int $threadId, int $page, int $perPage = 20): ?array;
    public function createThread(int $forumId, int $userId, string $title, string $content): int;
    public function createPost(int $threadId, int $userId, string $content): int;
    public function like(int $userId, string $type, int $targetId): bool;
    public function markThreadRead(int $userId, int $threadId): void;
    public function incrementViews(int $threadId): void;
    public function search(string $query, int $page, int $perPage): array;
    public function countSearch(string $query): int;
    public function clearCache(): void;
}