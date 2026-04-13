<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Service\NewsServiceInterface;
use GreyPanel\Service\SessionServiceInterface;

final class AdminNewsController
{
    public function __construct(
        private NewsServiceInterface $newsService,
        private SessionServiceInterface $session
    ) {}

    public function index(Request $request): Response
    {
        $page = (int)$request->get('page', 1);
        $news = $this->newsService->getPaginated($page, 20, false);
        $total = $this->newsService->count(false);
        return new Response(View::render('news/index.tpl', [
            'news_list' => $news,
            'total' => $total,
            'page' => $page,
            'per_page' => 20,
        ]));
    }

    public function form(Request $request, ?int $id = null): Response
    {
        $news = $id ? $this->newsService->getById($id) : null;
        if ($request->isPost()) {
            $data = [
                'title' => trim($request->post('title')),
                'slug' => trim($request->post('slug')),
                'content' => trim($request->post('content')),
                'is_published' => (bool)$request->post('is_published'),
            ];
            if ($id) {
                $this->newsService->update($id, $data);
            } else {
                $data['author_id'] = $this->session->getUserId();
                $id = $this->newsService->create($data);
            }
            return new RedirectResponse('/admin/news');
        }
        return new Response(View::render('news/form.tpl', ['news' => $news]));
    }

    public function delete(int $id): Response
    {
        $this->newsService->delete($id);
        return new RedirectResponse('/admin/news');
    }
}