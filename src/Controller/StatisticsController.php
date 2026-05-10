<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\SeoServiceInterface;
use GreyPanel\Service\StatisticsService;

class StatisticsController
{
    public function __construct(
        private StatisticsService $statsService,
        private SeoServiceInterface $seoService
    ) {
    }

    public function index(Request $request): Response
    {
        if (!$this->statsService->isAvailable()) {
            return new Response(View::render('statistics/unavailable.tpl'));
        }

        $page = (int)$request->get('page', 1);
        $sort = (int)$request->get('sort', 0);
        $search = trim((string)$request->get('search', ''));
        $perPage = 25;

        $players = $this->statsService->getRanking($page, $perPage, $sort, $search ?: null);
        $total = $this->statsService->getTotalPlayers($search ?: null);

        $meta = $this->seoService->getMetaTags('Статистика', 'Статистика игроков');

        return new Response(View::render('statistics/index.tpl', [
            'players' => $players,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'sort' => $sort,
            'search' => $search,
            'sort_types' => $this->statsService->getSortTypes(),
            'meta_title' => $meta['title'],
            'meta_description' => $meta['description'],
            'meta_keywords' => $meta['keywords'],
        ]));
    }

    public function player(Request $request, int $id): Response
    {
        if (!$this->statsService->isAvailable()) {
            return new Response(View::render('statistics/unavailable.tpl'));
        }

        $player = $this->statsService->getPlayerById($id);
        if (!$player) {
            return new Response('Игрок не найден', 404);
        }

        return new Response(View::render('statistics/player.tpl', [
            'player' => $player,
        ]));
    }
}
