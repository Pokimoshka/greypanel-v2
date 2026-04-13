<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class NewsRepository implements NewsRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('news');
    }

    public function findPaginated(int $page, int $perPage, bool $publishedOnly = true): array
    {
        $offset = ($page - 1) * $perPage;
        $where = $publishedOnly ? 'WHERE is_published = 1' : '';
        $sql = "SELECT n.*, u.username as author_name 
                FROM {$this->table} n
                LEFT JOIN {$this->db->table('users')} u ON n.author_id = u.id
                {$where}
                ORDER BY n.created_at DESC 
                LIMIT ? OFFSET ?";
        return $this->db->fetchAll($sql, [$perPage, $offset]);
    }

    public function count(bool $publishedOnly = true): int
    {
        $where = $publishedOnly ? 'WHERE is_published = 1' : '';
        $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM {$this->table} {$where}");
        return (int)($row['cnt'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT n.*, u.username as author_name 
             FROM {$this->table} n
             LEFT JOIN {$this->db->table('users')} u ON n.author_id = u.id
             WHERE n.id = ?",
            [$id]
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetchOne(
            "SELECT n.*, u.username as author_name 
             FROM {$this->table} n
             LEFT JOIN {$this->db->table('users')} u ON n.author_id = u.id
             WHERE n.slug = ?",
            [$slug]
        );
    }

    public function create(array $data): int
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (title, slug, content, author_id, is_published, views, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, 0, ?, ?)",
            [
                $data['title'],
                $data['slug'],
                $data['content'],
                $data['author_id'],
                (int)($data['is_published'] ?? 1),
                $now,
                $now
            ]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $now = time();
        $this->db->query(
            "UPDATE {$this->table} SET 
                title = ?, slug = ?, content = ?, is_published = ?, updated_at = ?
             WHERE id = ?",
            [
                $data['title'],
                $data['slug'],
                $data['content'],
                (int)($data['is_published'] ?? 1),
                $now,
                $id
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function incrementViews(int $id): void
    {
        $this->db->query("UPDATE {$this->table} SET views = views + 1 WHERE id = ?", [$id]);
    }
}