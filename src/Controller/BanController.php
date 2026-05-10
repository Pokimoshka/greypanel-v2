<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Repository\ForumThreadRepositoryInterface;
use GreyPanel\Interface\Repository\LogRepositoryInterface;
use GreyPanel\Interface\Repository\MoneyLogRepositoryInterface;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\BanServiceInterface;
use GreyPanel\Interface\Service\SeoServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Service\SessionService;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BanController extends AbstractController
{
    public function __construct(
        private BanServiceInterface $banService,
        private ForumThreadRepositoryInterface $threadRepo,
        private UserRepositoryInterface $userRepo,
        private MoneyLogRepositoryInterface $moneyLogRepo,
        private LogRepositoryInterface $logRepo,
        private SessionService $session,
        private SeoServiceInterface $seoService,
        private SettingsServiceInterface $settings,
        private LockFactory $lockFactory,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        parent::__construct($serializer, $validator, $translator);
    }

    public function index(Request $request): Response
    {
        $page = $request->getInt('page', 1);
        $perPage = 20;
        $search = trim($request->getString('search', ''));
        $status = $request->get('status') !== null ? $request->getInt('status') : null;

        try {
            $bans = $this->banService->getBans($page, $perPage, $search ?: null, $status);
            $total = $this->banService->countBans($search ?: null, $status);
        } catch (\Throwable $e) {
            $bans = [];
            $total = 0;
        }

        $meta = $this->seoService->getMetaTags('Бан-лист', 'Список забаненных игроков');

        return new Response(View::render('bans/index.tpl', [
            'bans' => $bans,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'status_filter' => $status,
            'meta_title' => $meta['title'],
            'meta_description' => $meta['description'],
            'meta_keywords' => $meta['keywords'],
        ]));
    }

    public function requestUnban(Request $request): JsonResponse
    {
        if (!$request->isPost()) {
            return $this->json(['error' => 'Invalid method'], 405);
        }
        $userId = $this->session->getUser()?->getId();
        if (!$userId) {
            return $this->json(['error' => $this->translator->trans('bans.unauthorized')]);
        }

        $banId = $request->postInt('ban_id');
        $demoUrl = trim($request->postString('demo_url', ''));

        $bans = $this->banService->getPaginatedBans(1, 1000);
        $ban = null;
        foreach ($bans as $b) {
            if ($b['bid'] == $banId) {
                $ban = $b;
                break;
            }
        }
        if (!$ban) {
            return $this->json(['error' => $this->translator->trans('bans.not_found')]);
        }

        $forumId = $this->settings->getInt('banlist_forum');
        if (!$forumId) {
            return $this->json(['error' => $this->translator->trans('bans.forum_not_set')]);
        }

        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return $this->json(['error' => 'Пользователь не найден']);
        }

        $nick = htmlspecialchars($ban['player_nick'], ENT_QUOTES, 'UTF-8');
        $server = htmlspecialchars($ban['server_name'], ENT_QUOTES, 'UTF-8');
        $admin = htmlspecialchars($ban['admin_nick'], ENT_QUOTES, 'UTF-8');
        $reason = htmlspecialchars($ban['ban_reason'], ENT_QUOTES, 'UTF-8');

        $content = "Здравствуйте, прошу разбанить меня {$nick}\n";
        $content .= "На сервере {$server}\n";
        $content .= "Меня забанил админ {$admin}\n";
        $content .= "Причина: {$reason}\n";
        if ($demoUrl) {
            $content .= "Демо: [url]{$demoUrl}[/url]\n";
        }
        $content .= "\n(Сообщение сгенерировано автоматически)";

        $threadId = $this->threadRepo->create($forumId, $userId, 'Заявка на разбан от ' . $user->getUsername(), $content);
        if ($threadId) {
            return $this->json(['success' => true, 'thread_id' => $threadId]);
        } else {
            return $this->json(['error' => $this->translator->trans('bans.create_thread_failed')]);
        }
    }

    public function paidUnban(Request $request): JsonResponse
    {
        if (!$request->isPost()) {
            return $this->json(['error' => 'Invalid method'], 405);
        }
        $userId = $this->session->getUser()?->getId();
        if (!$userId) {
            return $this->json(['error' => $this->translator->trans('bans.unauthorized')]);
        }

        $banId = $request->postInt('ban_id');
        $price = $this->settings->getInt('buy_razban');
        if ($price <= 0) {
            return $this->json(['error' => $this->translator->trans('bans.paid_disabled')]);
        }

        $lock = $this->lockFactory->createLock('paid_unban_' . $banId, 10);
        if (!$lock->acquire()) {
            return $this->json(['error' => 'Повторите попытку позже']);
        }

        try {
            $user = $this->userRepo->findById($userId);
            if (!$user || $user->getMoney() < $price) {
                return $this->json(['error' => $this->translator->trans('bans.insufficient_funds')]);
            }

            $banDeleted = $this->banService->deleteBan($banId);
            if (!$banDeleted) {
                return $this->json(['error' => $this->translator->trans('bans.delete_failed')]);
            }

            $newMoney = $user->getMoney() - $price;
            $user->setMoney($newMoney);
            $this->userRepo->update($user);

            $this->moneyLogRepo->add($userId, $price, 'Платный разбан', 1);
            $this->logRepo->add($userId, 'paid_unban', "Снят бан ID {$banId}");

            return $this->json(['success' => true]);
        } finally {
            $lock->release();
        }
    }

    public function lastBans(Request $request): JsonResponse
    {
        $cache = new FilesystemAdapter('widget_bans', 0, ROOT_DIR . '/var/cache');
        $bans = $cache->get('last_bans', function (ItemInterface $item) use ($request) {
            $item->expiresAfter(60);
            $limit = ($request->getInt('limit'));
            return $this->banService->getBans(1, $limit);
        });

        return $this->json($bans);
    }
}
