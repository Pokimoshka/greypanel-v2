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
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Interface\Service\ThemeServiceInterface;
use GreyPanel\Repository\UserGroupRepository;
use GreyPanel\Service\SessionService;

class AdminController
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
        private SessionService $session,
        private EncryptionServiceInterface $encryption,
        private UserGroupRepository $groupRepo
    ) {
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

        // Проверяем, существует ли папка install (относительно корня проекта)
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
        $page = (int)$request->get('page', 1);
        $perPage = 20;
        $search = trim($request->get('search', ''));

        if ($search) {
            $users = $this->userRepo->search($search, $page, $perPage);
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

            $newGroupId = (int)$request->post('group_id');
            $money = (int)$request->post('money');
            $banned = (bool)$request->post('banned');
            $newPassword = $request->post('password');

            $newGroup = $this->groupRepo->findById($newGroupId);
            if (!$newGroup) {
                $_SESSION['flash_error'] = 'Группа не найдена';
                return new RedirectResponse('/admin/users');
            }

            // Проверка на повышение прав через PermissionService
            $adminFlags = $_SESSION['user_flags'] ?? '';
            $targetFlags = $newGroup->getFlags();
            foreach (str_split($targetFlags) as $c) {
                if (!str_contains($adminFlags, $c)) {
                    $_SESSION['flash_error'] = 'Вы не можете назначить группу с правами выше ваших.';
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
            $this->logRepo->add($this->session->getUserId(), 'edit_user', "Редактирован пользователь ID: {$id}");

            $diff = $money - $oldMoney;
            if ($diff != 0) {
                $type = ($diff > 0) ? 0 : 1;
                $amount = abs($diff);
                $title = 'Изменение баланса администратором';
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
        $page = (int)$request->get('page', 1);
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
            $wallet = trim($request->post('yoomoney_wallet'));
            $secret = trim($request->post('yoomoney_secret'));
            if ($secret !== '') {
                $encryptedSecret = $this->encryption->encrypt($secret);
                $this->settings->set('yoomoney_secret', $encryptedSecret);
            }
            $this->settings->set('yoomoney_wallet', $wallet);
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
            $theme = $request->post('theme');
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
            $theme = $request->post('theme');
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
            $this->settings->set('amxbans_active', $request->post('amxbans_active') ? '1' : '0');
            $this->settings->set('amxbans_host', $request->post('amxbans_host'));
            $this->settings->set('amxbans_db', $request->post('amxbans_db'));
            $this->settings->set('amxbans_user', $request->post('amxbans_user'));
            if ($pass = $request->post('amxbans_pass')) {
                $encrypted = $this->encryption->encrypt($pass);
                $this->settings->set('amxbans_pass', $encrypted);
            }
            $this->settings->set('amxbans_prefix', $request->post('amxbans_prefix'));
            $this->settings->set('amxbans_forum', $request->post('amxbans_forum'));
            $this->settings->set('buy_razban', $request->post('buy_razban'));
            return new RedirectResponse('/admin/bans/settings');
        }

        $settings = [
            'amxbans_active' => $this->settings->get('amxbans_active'),
            'amxbans_host' => $this->settings->get('amxbans_host'),
            'amxbans_db' => $this->settings->get('amxbans_db'),
            'amxbans_user' => $this->settings->get('amxbans_user'),
            'amxbans_pass' => $this->settings->get('amxbans_pass'),
            'amxbans_prefix' => $this->settings->get('amxbans_prefix'),
            'amxbans_forum' => $this->settings->get('amxbans_forum'),
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
        return new JsonResponse($data);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $file = $request->files()['image'] ?? null;
            if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
                return new JsonResponse(['error' => 'Файл не загружен'], 400);
            }

            // Проверка MIME через finfo (без finfo_close, т.к. объект освободится сам)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file->getPathname());
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mime, $allowedMimes)) {
                return new JsonResponse(['error' => 'Разрешены только изображения (JPEG, PNG, GIF, WEBP)'], 400);
            }

            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                throw new \RuntimeException('Не удалось создать директорию для загрузок');
            }

            $filename = uniqid() . '.webp';
            $targetPath = $uploadDir . $filename;

            // Создаём менеджер с драйвером GD (или Imagick, если предпочитаете)
            $manager = \Intervention\Image\ImageManager::usingDriver(
                \Intervention\Image\Drivers\Imagick\Driver::class
            );

            // Декодируем загруженный файл
            $image = $manager->decodeSplFileInfo($file);

            // Изменяем размер (максимальная ширина 1200, высота пропорционально)
            $image->scale(width: 1200);

            // Кодируем в WebP с качеством 85
            $encoded = $image->encodeUsingFormat(\Intervention\Image\Format::WEBP, quality: 85);

            // Сохраняем на диск
            $encoded->save($targetPath);

            return new JsonResponse(['url' => '/uploads/' . $filename]);
        } catch (\Throwable $e) {
            error_log('Upload error: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Ошибка сервера: ' . $e->getMessage()], 500);
        }
    }
}
