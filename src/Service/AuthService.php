<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Model\User;
use GreyPanel\Repository\UserRepositoryInterface;

final class AuthService implements AuthServiceInterface
{
    private UserRepositoryInterface $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function register(string $username, string $email, string $password, string $passwordConfirm, string $ip, int $referralId = 0): User|string
    {
        if (empty($username) || empty($email) || empty($password)) {
            return 'Заполните все поля';
        }
        if ($password !== $passwordConfirm) {
            return 'Пароли не совпадают';
        }
        if (strlen($username) < 3 || strlen($username) > 32) {
            return 'Логин должен быть от 3 до 32 символов';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Некорректный email';
        }
        if (strlen($password) < 4) {
            return 'Пароль должен быть не менее 4 символов';
        }
        if ($this->userRepo->findByUsername($username)) {
            return 'Пользователь с таким ником уже существует';
        }
        if ($this->userRepo->findByEmail($email)) {
            return 'Пользователь с таким email уже существует';
        }

        if ($referralId && !$this->userRepo->findById($referralId)) {
            $referralId = 0;
        }

        $user = new User([
            'username' => $username,
            'email' => $email,
            'group' => 0,
            'money' => 0,
            'all_money' => 0,
            'reg_data' => time(),
            'reg_ip' => $ip,
            'referral' => 0,
            'banned' => false,
        ]);
        $userId = $this->userRepo->create($user, $password, $referralId);
        return $this->userRepo->findById($userId);
    }

    public function login(string $login, string $password): User|string
    {
        $data = $this->userRepo->findByLoginWithHash($login);
        if (!$data) {
            return 'Неверный логин или пароль';
        }
        $user = $data['user'];
        $hash = $data['hash'];
        if (!password_verify($password, $hash)) {
            return 'Неверный логин или пароль';
        }
        if ($user->isBanned()) {
            return 'Ваш аккаунт заблокирован';
        }
        return $user;
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepo->findById($id);
    }

    public function setRememberToken(User $user): string
    {
        $token = bin2hex(random_bytes(32));
        $user->setRememberToken($token);
        $this->userRepo->update($user);
        return $token;
    }

    public function getUserByRememberToken(string $token): ?User
    {
        return $this->userRepo->findByRememberToken($token);
    }

    public function clearRememberToken(User $user): void
    {
        $user->setRememberToken(null);
        $this->userRepo->update($user);
    }
}