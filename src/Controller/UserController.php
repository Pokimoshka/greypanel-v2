<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Service\AuthService;
use GreyPanel\Repository\UserRepositoryInterface;
use GreyPanel\Service\AvatarService;
use GreyPanel\Service\SessionService;

class UserController
{
    public function __construct(
        private AuthService $auth,
        private UserRepositoryInterface $userRepo,
        private AvatarService $avatarService,
        private SessionService $session
    ) {}

    public function profile(Request $request): Response
    {
        if (!$this->session->getUserId()) {
            return new RedirectResponse('/login');
        }

        $user = $this->auth->getUserById($this->session->getUserId());
        if (!$user) {
            session_destroy();
            return new RedirectResponse('/login');
        }

        $html = View::render('user/profile.tpl', [
            'user' => $user,
        ]);
        return new Response($html);
    }

    public function settings(Request $request): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return new RedirectResponse('/login');
        }

        $user = $this->auth->getUserById($_SESSION['user_id']);
        if (!$user) {
            session_destroy();
            return new RedirectResponse('/login');
        }

        $error = null;
        $success = null;

        if ($request->isPost()) {
            $email = trim($request->post('email'));
            $password = $request->post('password');
            $passwordConfirm = $request->post('password_confirm');
            $avatar = $request->files()['avatar'] ?? null;

            if (!empty($email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Некорректный email';
                } elseif ($email !== $user->getEmail()) {
                    $existing = $this->userRepo->findByEmail($email);
                    if ($existing && $existing->getId() !== $user->getId()) {
                        $error = 'Этот email уже используется';
                    } else {
                        $user->setEmail($email);
                    }
                }
            }

            if (!empty($password) && !empty($passwordConfirm)) {
                if (strlen($password) < 4) {
                    $error = 'Пароль должен быть не менее 4 символов';
                } elseif ($password !== $passwordConfirm) {
                    $error = 'Пароли не совпадают';
                } else {
                    $this->userRepo->updatePassword($user->getId(), $password);
                    $success = 'Пароль успешно изменён';
                }
            }

            $avatarFile = $request->files()['avatar'] ?? null;

            if ($avatarFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && 
                $avatarFile->getError() === UPLOAD_ERR_OK) {
                
                $validationError = $this->avatarService->validate($avatarFile);
                
                if ($validationError === null) {
                    $this->avatarService->deleteOldAvatar($user->getAvatar());
                    $newAvatarPath = $this->avatarService->resizeAndSave($avatarFile, $user->getId());
                    $user->setAvatar($newAvatarPath);
                    $this->userRepo->update($user);
                    $_SESSION['user']['avatar'] = $newAvatarPath;
                    $_SESSION['user']['updated_at'] = $user->getUpdatedAt();
                    $success = $success ? $success . ' Аватар обновлён.' : 'Аватар обновлён.';
                    return new RedirectResponse('/settings?success=avatar');
                } else {
                    $error = $validationError;
                }
            }

            if (!$error) {
                $this->userRepo->update($user);
                $_SESSION['user']['email'] = $user->getEmail();
                $_SESSION['user']['avatar'] = $user->getAvatar();
                if (!$success) {
                    $success = 'Настройки сохранены';
                }
            }
        }

        $html = View::render('user/settings.tpl', [
            'user' => $user,
            'error' => $error,
            'success' => $success,
        ]);
        return new Response($html);
    }

    public function referrals(Request $request): Response
    {
        $userId = $this->session->getUserId();
        $referrals = $this->userRepo->getReferrals($userId);
        $earnings = $this->userRepo->getReferralEarnings($userId);
        $refLink = $_ENV['SITE_URL'] . '/register?ref=' . $userId;
        $html = View::render('user/referrals.tpl', [
            'referrals' => $referrals,
            'earnings' => $earnings,
            'ref_link' => $refLink,
        ]);
        return new Response($html);
    }
}