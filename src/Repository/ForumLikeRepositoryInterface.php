<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

interface ForumLikeRepositoryInterface
{
    public function hasLiked(int $userId, string $targetType, int $targetId): bool;
    public function addLike(int $userId, string $targetType, int $targetId): void;
    public function removeLike(int $userId, string $targetType, int $targetId): void;
    public function countLikes(string $targetType, int $targetId): int;
    public function findLikedUsers(string $targetType, int $targetId, int $limit = 10): array;
}