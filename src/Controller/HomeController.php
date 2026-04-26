<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Repository\ForumThreadRepositoryInterface;
use GreyPanel\Interface\Repository\OnlineRepositoryInterface;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\BanServiceInterface;
use GreyPanel\Interface\Service\SeoServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Service\CacheService;

final class HomeController
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private ForumThreadRepositoryInterface $threadRepo,
        private BanServiceInterface $banService,
        private OnlineRepositoryInterface $onlineRepo,
        private SessionServiceInterface $session,
        private SeoServiceInterface $seoService
    ) {
    }

    public function index(Request $request): Response
    {
        $homeCache = new CacheService('home');

        $lastTopics = $homeCache->get('last_topics', function () {
            return $this->threadRepo->findLast(5);
        }, 300);

        $topDonators = $homeCache->get('top_donators', function () {
            return $this->userRepo->findTopDonators(5);
        }, 600);
        $lastBans = $this->banService->getBans(1, 5);
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
