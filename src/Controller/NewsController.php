<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\NewsServiceInterface;

final class NewsController
{
    public function __construct(private NewsServiceInterface $newsService)
    {
    }

    public function index(Request $request): Response
    {
        $page = (int)$request->get('page', 1);
        $perPage = 10;
        $news = $this->newsService->getPaginated($page, $perPage);
        $total = $this->newsService->count();

        return new Response(View::render('news/index.tpl', [
            'news' => $news,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ]));
    }

    public function show(string $slug): Response
    {
        $news = $this->newsService->getBySlug($slug);
        if (!$news || !$news['is_published']) {
            return new Response('Новость не найдена', 404);
        }
        $this->newsService->incrementViews($news['id']);
        return new Response(View::render('news/show.tpl', ['news' => $news]));
    }
}
