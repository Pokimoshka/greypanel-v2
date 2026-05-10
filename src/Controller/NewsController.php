<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\NewsServiceInterface;
use GreyPanel\Interface\Service\SeoServiceInterface;

final class NewsController
{
    public function __construct(
        private NewsServiceInterface $newsService,
        private SeoServiceInterface $seoService
    ) {
    }

    public function index(Request $request): Response
    {
        $page = (int)$request->get('page', 1);
        $perPage = 10;
        $news = $this->newsService->getPaginated($page, $perPage);
        $total = $this->newsService->count();
        $meta = $this->seoService->getMetaTags('Новости', 'Список новостей');

        return new Response(View::render('news/index.tpl', [
            'news' => $news,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'meta_title' => $meta['title'],
            'meta_description' => $meta['description'],
            'meta_keywords' => $meta['keywords'],
        ]));
    }

    public function show(string $slug): Response
    {
        $news = $this->newsService->getBySlug($slug);
        if (!$news || !$news['is_published']) {
            return new Response('Новость не найдена', 404);
        }
        $this->newsService->incrementViews($news['id']);

        $meta = $this->seoService->getMetaTags($news['title'], mb_substr(strip_tags($news['content']), 0, 150));

        return new Response(View::render('news/show.tpl', [
            'news' => $news,
            'meta_title' => $meta['title'],
            'meta_description' => $meta['description'],
            'meta_keywords' => $meta['keywords'],
        ]));
    }
}
