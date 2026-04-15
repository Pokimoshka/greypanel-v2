<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\JsonResponse;
use GreyPanel\Service\BanServiceInterface;
use GreyPanel\Repository\ForumThreadRepositoryInterface;
use GreyPanel\Repository\UserRepositoryInterface;
use GreyPanel\Repository\MoneyLogRepositoryInterface;
use GreyPanel\Repository\LogRepositoryInterface;
use GreyPanel\Service\SessionService;

class BanController
{
    public function __construct(
        private BanServiceInterface $banService,
        private ForumThreadRepositoryInterface $threadRepo,
        private UserRepositoryInterface $userRepo,
        private MoneyLogRepositoryInterface $moneyLogRepo,
        private LogRepositoryInterface $logRepo,
        private SessionService $session
    ) {}

    public function index(Request $request): Response
    {
        $page = (int)$request->get('page', 1);
        $perPage = 20;
        $search = trim($request->get('search', ''));

        if ($search) {
            $bans = $this->banService->searchBans($search);
            $total = count($bans);
        } else {
            $bans = $this->banService->getBans($page, $perPage);
            $total = $this->banService->countBans();
        }

        $html = View::render('bans/index.tpl', [
            'bans' => $bans,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
        ]);
        return new Response($html);
    }

    public function requestUnban(Request $request): JsonResponse
    {
        if (!$request->isPost()) {
            return new JsonResponse(['error' => 'Invalid method'], 405);
        }
        $userId = $this->session->getUserId();
        if (!$userId) {
            return new JsonResponse(['error' => 'Необходимо авторизоваться']);
        }

        $banId = (int)$request->post('ban_id');
        $demoUrl = trim($request->post('demo_url', ''));

        $bans = $this->banService->findPaginated(1, 1000);
        $ban = null;
        foreach ($bans as $b) {
            if ($b['bid'] == $banId) {
                $ban = $b;
                break;
            }
        }
        if (!$ban) {
            return new JsonResponse(['error' => 'Бан не найден']);
        }

        $forumId = $this->settings->getInt('amxbans_forum');
        if (!$forumId) {
            return new JsonResponse(['error' => 'Форум для заявок не настроен']);
        }

        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'Пользователь не найден']);
        }

        $content = "Здравствуйте, прошу разбанить меня {$ban['player_nick']}\n";
        $content .= "На сервере {$ban['server_name']}\n";
        $content .= "Меня забанил админ {$ban['admin_nick']}\n";
        $content .= "Причина: {$ban['ban_reason']}\n";
        if ($demoUrl) {
            $content .= "Демо: [url]{$demoUrl}[/url]\n";
        }
        $content .= "\n(Сообщение сгенерировано автоматически)";

        $threadId = $this->threadRepo->create($forumId, $userId, 'Заявка на разбан от ' . $user->getUsername(), $content);
        if ($threadId) {
            return new JsonResponse(['success' => true, 'thread_id' => $threadId]);
        } else {
            return new JsonResponse(['error' => 'Ошибка создания темы']);
        }
    }

    public function paidUnban(Request $request): JsonResponse
    {
        if (!$request->isPost()) {
            return new JsonResponse(['error' => 'Invalid method'], 405);
        }
        $userId = $this->session->getUserId();
        if (!$userId) {
            return new JsonResponse(['error' => 'Необходимо авторизоваться']);
        }

        $banId = (int)$request->post('ban_id');
        $price = $this->settings->getInt('buy_razban');
        if ($price <= 0) {
            return new JsonResponse(['error' => 'Платный разбан отключён']);
        }

        $user = $this->userRepo->findById($userId);
        if (!$user || $user->getMoney() < $price) {
            return new JsonResponse(['error' => 'Недостаточно средств']);
        }

        $banDeleted = $this->banService->deleteBan($banId);
        if (!$banDeleted) {
            return new JsonResponse(['error' => 'Не удалось удалить бан']);
        }

        $newMoney = $user->getMoney() - $price;
        $user->setMoney($newMoney);
        $this->userRepo->update($user);

        $_SESSION['user']['money'] = $newMoney;

        $this->moneyLogRepo->add($userId, $price, 'Платный разбан', 1);
        $this->logRepo->add($userId, 'paid_unban', "Снят бан ID {$banId}");

        return new JsonResponse(['success' => true]);
    }

    public function lastBans(Request $request): JsonResponse
    {
        $limit = (int)($request->get('limit') ?? 5);
        $bans = $this->banService->getBans(1, $limit);
        return new JsonResponse($bans);
    }
}