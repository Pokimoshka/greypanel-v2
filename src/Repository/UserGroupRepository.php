<?php

declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;
use GreyPanel\Model\UserGroup;

class UserGroupRepository
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('user_groups');
    }

    public function findById(int $id): ?UserGroup
    {
        $row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        return $row ? new UserGroup($row) : null;
    }

    public function findDefault(): ?UserGroup
    {
        $row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE is_default = 1 LIMIT 1");
        return $row ? new UserGroup($row) : null;
    }

    public function findAll(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM {$this->table} ORDER BY id ASC");
        return array_map(fn ($row) => new UserGroup($row), $rows);
    }

    public function create(UserGroup $group): int
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (name, flags, is_default, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
            [$group->getName(), $group->getFlags(), (int)$group->isDefault(), $now, $now]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(UserGroup $group): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET name = ?, flags = ?, is_default = ?, updated_at = ? WHERE id = ?",
            [$group->getName(), $group->getFlags(), (int)$group->isDefault(), time(), $group->getId()]
        );
    }

    public function delete(int $id): void
    {
        // Проверка, не используется ли группа в users
        $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM {$this->db->table('users')} WHERE group_id = ?", [$id]);
        if ($row && $row['cnt'] > 0) {
            throw new \RuntimeException('Невозможно удалить группу, так как есть пользователи, состоящие в ней');
        }
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}
