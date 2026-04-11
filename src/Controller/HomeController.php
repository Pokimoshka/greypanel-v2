<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Repository\UserRepositoryInterface;
use GreyPanel\Repository\ForumThreadRepositoryInterface;
use GreyPanel\Repository\BanRepositoryInterface;
use GreyPanel\Repository\OnlineRepositoryInterface;
use GreyPanel\Service\SessionServiceInterface;
use GreyPanel\Service\SeoServiceInterface;

final class HomeController
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private ForumThreadRepositoryInterface $threadRepo,
        private BanRepositoryInterface $banRepo,
        private OnlineRepositoryInterface $onlineRepo,
        private SessionServiceInterface $session,
        private SeoServiceInterface $seoService
    ) {}

    public function index(Request $request): Response
    {
        $lastTopics = $this->threadRepo->findLast(5);
        $topDonators = $this->userRepo->findTopDonators(5);
        $lastBans = $this->banRepo->findPaginated(1, 5);
        $onlineUsers = $this->onlineRepo->findOnlineUsers();
        $meta = $this->seoService->getMetaTags('Главная страница', 'Добро пожаловать на GreyPanel');

        return new Response(View::render('home.tpl', [
            'site_name' => $_ENV['SITE_NAME'] ?? 'GreyPanel',
            'app' => [
                'user' => $this->session->getUser()
            ],
            'last_topics' => $lastTopics,
            'top_donators' => $topDonators,
            'last_bans' => $lastBans,
            'online_users' => $onlineUsers,
            'meta_title' => $meta['title'],
            'meta_description' => $meta['description'],
            'meta_keywords' => $meta['keywords'],
        ]));
    }
}