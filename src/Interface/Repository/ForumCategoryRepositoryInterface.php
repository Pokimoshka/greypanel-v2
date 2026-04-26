<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

interface ForumCategoryRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?array;
    public function create(string $title, string $description, int $sortOrder = 0): int;
    public function update(int $id, string $title, string $description, int $sortOrder): void;
    public function delete(int $id): void;
    public function updateSortOrder(int $id, int $order): void;
}
