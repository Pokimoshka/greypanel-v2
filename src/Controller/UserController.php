<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Dto\ProfileUpdateDto;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Service\AuthService;
use GreyPanel\Service\AvatarService;
use GreyPanel\Service\LocaleManager;
use GreyPanel\Service\SiteService;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    public function __construct(
        private AuthService $auth,
        private UserRepositoryInterface $userRepo,
        private AvatarService $avatarService,
        private SessionServiceInterface $session,
        private SiteService $siteService,
        private LocaleManager $localeManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        parent::__construct($serializer, $validator, $translator);
    }

    public function profile(Request $request, ?int $id = null): Response
    {
        if ($id === null && !$this->session->isLoggedIn()) {
            return new RedirectResponse('/login');
        }

        if ($id === null) {
            $id = $this->session->getUser()->getId();
        }

        $user = $this->userRepo->findById($id);
        if (!$user) {
            return new Response('Пользователь не найден', 404);
        }

        return new Response(View::render('user/profile.tpl', ['user' => $user]));
    }

    public function settings(Request $request): Response
    {
        if (!$this->session->isLoggedIn()) {
            return new RedirectResponse('/login');
        }

        $userId = $this->session->getUser()?->getId();
        $user = $this->auth->getUserById($userId);
        if (!$user) {
            $this->session->clear();
            return new RedirectResponse('/login');
        }

        $error = null;
        $success = null;

        if ($request->isPost()) {
            $dto = new ProfileUpdateDto([
                'email' => $request->postString('email'),
                'password' => $request->postString('password'),
                'password_confirm' => $request->postString('password_confirm'),
            ]);

            $violations = $this->validator->validate($dto);
            $errors = [];
            foreach ($violations as $v) {
                $errors[$v->getPropertyPath()] = $v->getMessage();
            }

            if (!empty($dto->password) && $dto->password !== $dto->passwordConfirm) {
                $errors['password'] = $this->translator->trans('profile.password_mismatch', [], 'validators');
            }

            if ($dto->email && $dto->email !== $user->getEmail()) {
                $existing = $this->userRepo->findByEmail($dto->email);
                if ($existing && $existing->getId() !== $user->getId()) {
                    $errors['email'] = $this->translator->trans('profile.email_taken', [], 'validators');
                }
            }

            if (!empty($errors)) {
                $error = reset($errors);
            } else {
                if ($dto->email) {
                    $user->setEmail($dto->email);
                }
                if ($dto->password) {
                    $this->userRepo->updatePassword($user->getId(), $dto->password);
                    $success = $this->translator->trans('settings.password_changed');
                }

                $lang = $request->postString('lang');
                if (in_array($lang, ['ru', 'en'])) {
                    $user->setLang($lang);
                    $success = $this->translator->trans('settings.language_changed');
                }
                $this->userRepo->update($user);
                if (!$success) {
                    $success = $this->translator->trans('settings.settings_saved');
                }
            }
        }

        return new Response(View::render('user/settings.tpl', [
            'user' => $user,
            'error' => $error,
            'success' => $success,
        ]));
    }

    public function referrals(Request $request): Response
    {
        $userId = $this->session->getUser()?->getId();
        $referrals = $this->userRepo->getReferrals($userId);
        $earnings = $this->userRepo->getReferralEarnings($userId);
        $refLink = $this->siteService->getSiteUrl() . '/register?ref=' . $userId;
        return new Response(View::render('user/referrals.tpl', [
            'referrals' => $referrals,
            'earnings' => $earnings,
            'ref_link' => $refLink,
        ]));
    }

    public function topDonators(Request $request): JsonResponse
    {
        $cache = new FilesystemAdapter('widget_donators', 0, ROOT_DIR . '/var/cache');
        $donators = $cache->get('top_donators', function (ItemInterface $item) use ($request) {
            $item->expiresAfter(300);
            $limit = ($request->getInt('limit'));
            return $this->userRepo->findTopDonators($limit);
        });

        return $this->json($donators);
    }
}
