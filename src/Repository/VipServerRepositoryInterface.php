<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

interface VipServerRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): void;
    public function delete(int $id): void;
}