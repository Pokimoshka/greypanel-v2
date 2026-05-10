<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Dto\RegisterDto;
use GreyPanel\Exception\AuthenticationException;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\AuthServiceInterface;
use GreyPanel\Model\User;
use GreyPanel\Model\UserGroup;
use GreyPanel\Repository\UserGroupRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private UserGroupRepository $groupRepo,
        private TranslatorInterface $translator,
        private ValidatorInterface $validator
    ) {
    }

    public function register(string $username, string $email, string $password, string $passwordConfirm, string $ip, int $referralId = 0): User
    {
        $dto = new RegisterDto(compact('username', 'email', 'password'));

        $violations = $this->validator->validate($dto);
        $errors = [];
        foreach ($violations as $v) {
            $errors[$v->getPropertyPath()] = $v->getMessage();
        }

        if ($password !== $passwordConfirm) {
            $errors['password'] = $this->translator->trans('auth.password_mismatch', [], 'validators');
        }
        if ($referralId && !$this->userRepo->findById($referralId)) {
            $referralId = 0;
        }

        if (!empty($errors)) {
            $firstError = reset($errors);
            throw new AuthenticationException($firstError);
        }

        $defaultGroup = $this->groupRepo->findDefault();
        if (!$defaultGroup) {
            $defaultGroup = new UserGroup([
                'name' => 'Пользователь',
                'flags' => '',
                'is_default' => true,
            ]);
            $this->groupRepo->create($defaultGroup);
            $defaultGroup = $this->groupRepo->findDefault();
        }

        $user = new User([
            'username' => $username,
            'email' => $email,
            'money' => 0,
            'all_money' => 0,
            'reg_data' => time(),
            'reg_ip' => $ip,
            'referral' => 0,
            'banned' => false,
        ]);
        $user->setGroup($defaultGroup);
        $userId = $this->userRepo->create($user, $password, $referralId);

        return $this->userRepo->findById($userId);
    }

    public function login(string $login, string $password): User
    {
        $data = $this->userRepo->findByLoginWithHash($login);
        if (!$data) {
            throw new AuthenticationException($this->translator->trans('auth.login_failed'));
        }
        $user = $data['user'];
        $hash = $data['hash'];
        if (!password_verify($password, $hash)) {
            throw new AuthenticationException($this->translator->trans('auth.login_failed'));
        }
        if ($user->isBanned()) {
            throw new AuthenticationException($this->translator->trans('auth.account_banned'));
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
