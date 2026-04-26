<?php

declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Model\User;

final class UserRepository implements UserRepositoryInterface
{
    private Database $db;
    private string $table;
    private UserGroupRepository $groupRepo;

    public function __construct(Database $db, UserGroupRepository $groupRepo)
    {
        $this->db = $db;
        $this->table = $db->table('users');
        $this->groupRepo = $groupRepo;
    }

    /**
     * Создаёт объект User из массива БД с полной загрузкой группы.
     */
    private function hydrateUser(array $row): ?User
    {
        if (!$row) {
            return null;
        }
        $user = new User($row);
        $group = $this->groupRepo->findById((int) $row['group_id']);
        $user->setGroup($group);
        return $user;
    }

    public function findById(int $id): ?User
    {
        $row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        return $this->hydrateUser($row);
    }

    public function findByUsername(string $username): ?User
    {
        $row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE username = ?", [$username]);
        return $this->hydrateUser($row);
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE email = ?", [$email]);
        return $this->hydrateUser($row);
    }

    public function findByRememberToken(string $token): ?User
    {
        $row = $this->db->fetchOne("SELECT * FROM {$this->table} WHERE remember_token = ?", [$token]);
        return $this->hydrateUser($row);
    }

    public function findByLoginWithHash(string $login): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE username = ? OR email = ?",
            [$login, $login]
        );
        if (!$row) {
            return null;
        }
        return [
            'user' => $this->hydrateUser($row),
            'hash' => $row['password_hash'],
        ];
    }

    public function create(User $user, string $plainPassword, int $referralId = 0): int
    {
        $hashed = password_hash($plainPassword, PASSWORD_DEFAULT);
        $now = time();
        $sql = "INSERT INTO {$this->table}
                (username, password_hash, email, group_id, money, all_money, avatar, reg_data, reg_ip, referral, banned, created_at, updated_at, count_thread, count_post, count_like)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $user->getUsername(),
            $hashed,
            $user->getEmail(),
            $user->getGroupId(),
            $user->getMoney(),
            $user->getAllMoney(),
            $user->getAvatar(),
            $user->getRegData(),
            $user->getRegIp(),
            $referralId,
            (int) $user->isBanned(),
            $now,
            $now,
            $user->getCountThread(),
            $user->getCountPost(),
            $user->getCountLike(),
        ];
        $this->db->query($sql, $params);
        return (int) $this->db->getPdo()->lastInsertId();
    }

    public function update(User $user): void
    {
        $sql = "UPDATE {$this->table} SET
                username = ?, email = ?, group_id = ?, money = ?, all_money = ?, avatar = ?,
                banned = ?, remember_token = ?, updated_at = ?, count_thread = ?, count_post = ?, count_like = ?
                WHERE id = ?";
        $params = [
            $user->getUsername(),
            $user->getEmail(),
            $user->getGroupId(),
            $user->getMoney(),
            $user->getAllMoney(),
            $user->getAvatar(),
            (int) $user->isBanned(),
            $user->getRememberToken(),
            time(),
            $user->getCountThread(),
            $user->getCountPost(),
            $user->getCountLike(),
            $user->getId(),
        ];
        $this->db->query($sql, $params);
    }

    public function updatePassword(int $userId, string $plainPassword): void
    {
        $hashed = password_hash($plainPassword, PASSWORD_DEFAULT);
        $this->db->query(
            "UPDATE {$this->table} SET password_hash = ?, updated_at = ? WHERE id = ?",
            [$hashed, time(), $userId]
        );
    }

    public function count(): int
    {
        $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM {$this->table}");
        return (int) ($row['cnt'] ?? 0);
    }

    public function findAllPaginated(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $rows = $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        return array_map(fn ($row) => $this->hydrateUser($row), $rows);
    }

    public function findBySearchPaginated(string $query, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $like = "%{$query}%";
        $rows = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE username LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?",
            [$like, $like, $perPage, $offset]
        );
        return array_map(fn ($row) => $this->hydrateUser($row), $rows);
    }

    public function countSearch(string $query): int
    {
        $like = "%{$query}%";
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE username LIKE ? OR email LIKE ?",
            [$like, $like]
        );
        return (int) ($row['cnt'] ?? 0);
    }

    public function getReferrals(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT id, username, email, reg_data FROM {$this->table} WHERE referral = ? ORDER BY id DESC",
            [$userId]
        );
    }

    public function getReferralEarnings(int $userId): int
    {
        $row = $this->db->fetchOne(
            "SELECT referral_earnings FROM {$this->table} WHERE id = ?",
            [$userId]
        );
        return $row ? (int) $row['referral_earnings'] : 0;
    }

    public function addReferralEarnings(int $userId, int $amount): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET referral_earnings = referral_earnings + ? WHERE id = ?",
            [$amount, $userId]
        );
    }

    public function updateReferral(int $userId, int $referralId): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET referral = ? WHERE id = ?",
            [$referralId, $userId]
        );
    }

    public function findTopDonators(int $limit): array
    {
        return $this->db->fetchAll(
            "SELECT id, username, avatar, all_money FROM {$this->table} WHERE all_money > 0 ORDER BY all_money DESC LIMIT ?",
            [$limit]
        );
    }

    public function getRegistrationsLastDays(int $days): array
    {
        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = $date;
            $start = strtotime($date);
            $end = $start + 86400;
            $stmt = $this->db->query(
                "SELECT COUNT(*) as cnt FROM {$this->table} WHERE created_at >= ? AND created_at < ?",
                [$start, $end]
            );
            $row = $stmt->fetch();
            $values[] = (int) ($row['cnt'] ?? 0);
        }
        return ['labels' => $labels, 'values' => $values];
    }
}
