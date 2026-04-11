<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class ForumCategoryRepository implements ForumCategoryRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('forum_categories');
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} ORDER BY sort_order ASC");
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function create(string $title, string $description, int $sortOrder = 0): int
    {
        $this->db->query(
            "INSERT INTO {$this->table} (title, description, sort_order) VALUES (?, ?, ?)",
            [$title, $description, $sortOrder]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(int $id, string $title, string $description, int $sortOrder): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET title = ?, description = ?, sort_order = ? WHERE id = ?",
            [$title, $description, $sortOrder, $id]
        );
    }

    public function delete(int $id): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function updateSortOrder(int $id, int $order): void
    {
        $this->db->query("UPDATE {$this->table} SET sort_order = ? WHERE id = ?", [$order, $id]);
    }
}