<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

interface NewsRepositoryInterface
{
    public function findPaginated(int $page, int $perPage, bool $publishedOnly = true): array;
    public function count(bool $publishedOnly = true): int;
    public function findById(int $id): ?array;
    public function findBySlug(string $slug): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): void;
    public function delete(int $id): void;
    public function incrementViews(int $id): void;
}