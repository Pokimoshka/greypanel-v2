<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

interface ForumForumRepositoryInterface
{
    public function findByCategoryId(int $categoryId): array;
    public function findById(int $id): ?array;
    public function findAll(): array;
    public function create(int $categoryId, string $title, string $description, string $icon, int $sortOrder = 0): int;
    public function update(int $id, int $categoryId, string $title, string $description, string $icon, int $sortOrder): void;
    public function delete(int $id): void;
    public function updateSortOrder(int $id, int $order): void;
}
