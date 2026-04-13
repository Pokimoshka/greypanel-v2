<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface NewsServiceInterface
{
    public function getPaginated(int $page, int $perPage, bool $publishedOnly = true): array;
    public function count(bool $publishedOnly = true): int;
    public function getBySlug(string $slug): ?array;
    public function getById(int $id): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): void;
    public function delete(int $id): void;
    public function incrementViews(int $id): void;
}