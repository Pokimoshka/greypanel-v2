<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Repository\ForumForumRepositoryInterface;
use GreyPanel\Interface\Repository\ForumThreadRepositoryInterface;
use GreyPanel\Interface\Repository\LogRepositoryInterface;
use GreyPanel\Interface\Repository\MoneyLogRepositoryInterface;
use GreyPanel\Interface\Repository\OnlineRepositoryInterface;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Interface\Service\PermissionServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Interface\Service\ThemeServiceInterface;
use GreyPanel\Repository\UserGroupRepository;
use GreyPanel\Service\ImageUploadService;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminController extends AbstractController
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private ForumThreadRepositoryInterface $threadRepo,
        private LogRepositoryInterface $logRepo,
        private SettingsServiceInterface $settings,
        private MoneyLogRepositoryInterface $moneyLogRepo,
        private ThemeServiceInterface $themeService,
        private ForumForumRepositoryInterface $forumForumRepo,
        private OnlineRepositoryInterface $onlineRepo,
        private SessionServiceInterface $session,
        private EncryptionServiceInterface $encryption,
        private UserGroupRepository $groupRepo,
        private PermissionServiceInterface $permissionService,
        private ImageUploadService $imageUploadService,
        TranslatorInterface $translator,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->translator = $translator;
        parent::__construct($serializer, $validator, $translator);
    }

    public function index(Request $request): Response
    {
        $totalUsers = $this->userRepo->count();
        $totalThreads = $this->threadRepo->countAll();
        $totalVip = 0;
        $onlineUsers = $this->onlineRepo->findOnlineUsers();
        $online_count = count($onlineUsers);
        $recentLogs = $this->logRepo->findPaginated(1, 5);
        $recentUsers = $this->userRepo->findAllPaginated(1, 5);

        $installPath = ROOT_DIR . '/install';
        $installExists = is_dir($installPath);

        $html = View::render('index.tpl', [
            'total_users' => $totalUsers,
            'total_threads' => $totalThreads,
            'total_vip' => $totalVip,
            'online_count' => $online_count,
            'recent_logs' => $recentLogs,
            'recent_users' => $recentUsers,
            'install_exists' => $installExists,
        ]);
        return new Response($html);
    }

    public function users(Request $request): Response
    {
        $page = $request->getInt('page', 1);
        $perPage = 20;
        $search = trim($request->getString('search', ''));

        if ($search) {
            $users = $this->userRepo->findBySearchPaginated($search, $page, $perPage);
            $total = $this->userRepo->countSearch($search);
        } else {
            $users = $this->userRepo->findAllPaginated($page, $perPage);
            $total = $this->userRepo->count();
        }

        $html = View::render('users.tpl', [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
        ]);
        return new Response($html);
    }

    public function userEdit(Request $request, int $id): Response
    {
        $user = $this->userRepo->findById($id);
        if (!$user) {
            return new RedirectResponse('/admin/users');
        }

        $groups = $this->groupRepo->findAll();

        if ($request->isPost()) {
            $oldMoney = $user->getMoney();

            $newGroupId = $request->postInt('group_id');
            $money = $request->postInt('money');
            $banned = $request->postBool('banned');
            $newPassword = $request->postString('password');

            $newGroup = $this->groupRepo->findById($newGroupId);
            if (!$newGroup) {
                $this->session->setFlash('error', $this->translator->trans('admin.group_not_found'));
                return new RedirectResponse('/admin/users');
            }

            $adminFlags = $this->permissionService->getFlags();
            $targetFlags = $newGroup->getFlags();
            foreach (str_split($targetFlags) as $c) {
                if (!str_contains($adminFlags, $c)) {
                    $this->session->setFlash('error', $this->translator->trans('admin.cannot_assign_higher_group'));
                    return new RedirectResponse('/admin/users');
                }
            }

            $user->setGroup($newGroup);
            $user->setMoney($money);
            $user->setBanned($banned);

            if (!empty($newPassword)) {
                $this->userRepo->updatePassword($user->getId(), $newPassword);
            }

            $this->userRepo->update($user);
            $this->logRepo->add(
                $this->session->getUser()->getId(),
                'edit_user',
                $this->translator->trans('admin.user_edited', ['%id%' => $id])
            );

            $diff = $money - $oldMoney;
            if ($diff != 0) {
                $type = ($diff > 0) ? 0 : 1;
                $amount = abs($diff);
                $title = $this->translator->trans('admin.balance_change');
                $this->moneyLogRepo->add($user->getId(), $amount, $title, $type);
            }

            return new RedirectResponse('/admin/users');
        }

        return new Response(View::render('user_edit.tpl', [
            'user' => $user,
            'groups' => $groups,
        ]));
    }

    public function logs(Request $request): Response
    {
        $page = $request->getInt('page', 1);
        $perPage = 30;
        $logs = $this->logRepo->findPaginated($page, $perPage);
        $total = $this->logRepo->count();
        $html = View::render('logs.tpl', [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ]);
        return new Response($html);
    }

    public function paymentSettings(Request $request): Response
    {
        if ($request->isPost()) {
            $wallet = trim($request->postString('yoomoney_wallet'));
            $secret = trim($request->postString('yoomoney_secret'));

            if ($secret !== '') {
                $encryptedSecret = $this->encryption->encrypt($secret);
                $this->settings->set('yoomoney_secret', $encryptedSecret);
            }
            $this->settings->set('yoomoney_wallet', $wallet);
            $this->session->setFlash('success', $this->translator->trans('admin.payment_settings_saved'));
            return new RedirectResponse('/admin/payments');
        }

        $wallet = $this->settings->get('yoomoney_wallet');
        $encryptedSecret = $this->settings->get('yoomoney_secret');
        $secret = $encryptedSecret ? $this->encryption->decrypt($encryptedSecret) : '';

        $html = View::render('payment_settings.tpl', [
            'wallet' => $wallet,
            'secret' => $secret,
        ]);
        return new Response($html);
    }

    public function themes(Request $request): Response
    {
        if ($request->isPost()) {
            $theme = $request->postString('theme');
            if ($this->themeService->setActiveTheme($theme)) {
                return new RedirectResponse('/admin/themes?success=1');
            } else {
                return new RedirectResponse('/admin/themes?error=1');
            }
        }

        $themes = $this->themeService->getThemes();
        $active = $this->themeService->getActiveTheme();

        $html = View::render('theme_settings.tpl', [
            'themes' => $themes,
            'active' => $active,
        ]);
        return new Response($html);
    }

    public function themeSettings(Request $request): Response
    {
        if ($request->isPost()) {
            $theme = $request->postString('theme');
            $this->themeService->setActiveTheme($theme);
            return new RedirectResponse('/admin/theme');
        }
        $themes = $this->themeService->getThemes();
        $current = $this->themeService->getActiveTheme();

        return new Response(View::render('theme_settings.tpl', [
            'themes' => $themes,
            'current' => $current,
        ]));
    }

    public function banSettings(Request $request): Response
    {
        if ($request->isPost()) {
            $this->settings->set('banlist_active', $request->postBool('banlist_active') ? '1' : '0');
            $this->settings->set('banlist_host', $request->postString('banlist_host'));
            $this->settings->set('banlist_db', $request->postString('banlist_db'));
            $this->settings->set('banlist_user', $request->postString('banlist_user'));
            if ($pass = $request->postString('banlist_pass')) {
                $encrypted = $this->encryption->encrypt($pass);
                $this->settings->set('banlist_pass', $encrypted);
            }
            $this->settings->set('banlist_prefix', $request->postString('banlist_prefix'));
            $this->settings->set('banlist_forum', $request->postString('banlist_forum'));
            $this->settings->set('buy_razban', $request->postString('buy_razban'));
            $this->session->setFlash('success', $this->translator->trans('admin.ban_settings_saved'));
            return new RedirectResponse('/admin/bans/settings');
        }

        $settings = [
            'banlist_active' => $this->settings->get('banlist_active'),
            'banlist_host' => $this->settings->get('banlist_host'),
            'banlist_db' => $this->settings->get('banlist_db'),
            'banlist_user' => $this->settings->get('banlist_user'),
            'banlist_pass' => $this->settings->get('banlist_pass'),
            'banlist_prefix' => $this->settings->get('banlist_prefix'),
            'banlist_forum' => $this->settings->get('banlist_forum'),
            'buy_razban' => $this->settings->get('buy_razban'),
        ];

        $forums = $this->forumForumRepo->findAll();

        $html = View::render('ban_settings.tpl', [
            'settings' => $settings,
            'forums' => $forums,
        ]);
        return new Response($html);
    }

    public function statsRegistrations(Request $request): JsonResponse
    {
        $data = $this->userRepo->getRegistrationsLastDays(7);
        return $this->json($data);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $file = $request->files()['image'] ?? null;
            if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
                return $this->json(['error' => $this->translator->trans('admin.upload_no_file')], 400);
            }

            $url = $this->imageUploadService->upload($file, 'uploads');
            return $this->json(['url' => $url]);
        } catch (\Throwable $e) {
            error_log('Upload error: ' . $e->getMessage());
            return $this->json(['error' => $this->translator->trans('admin.upload_server_error', ['%message%' => $e->getMessage()])], 500);
        }
    }
}
