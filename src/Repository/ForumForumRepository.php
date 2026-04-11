<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class ForumForumRepository implements ForumForumRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('forum_forums');
    }

    public function findByCategoryId(int $categoryId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE category_id = ? ORDER BY sort_order ASC",
            [$categoryId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} ORDER BY sort_order ASC");
    }

    public function create(int $categoryId, string $title, string $description, string $icon, int $sortOrder = 0): int
    {
        $this->db->query(
            "INSERT INTO {$this->table} (category_id, title, description, icon, sort_order) VALUES (?, ?, ?, ?, ?)",
            [$categoryId, $title, $description, $icon, $sortOrder]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(int $id, int $categoryId, string $title, string $description, string $icon, int $sortOrder): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET category_id = ?, title = ?, description = ?, icon = ?, sort_order = ? WHERE id = ?",
            [$categoryId, $title, $description, $icon, $sortOrder, $id]
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