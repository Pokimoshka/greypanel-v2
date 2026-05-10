<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\NewsServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminNewsController
{
    public function __construct(
        private NewsServiceInterface $newsService,
        private SessionServiceInterface $session,
        private TranslatorInterface $translator
    ) {
    }

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
        $news = null;
        if ($id) {
            $news = $this->newsService->getByIdRaw($id);
        }
        if ($request->isPost()) {
            $data = [
                'title' => trim($request->postString('title')),
                'slug' => trim($request->postString('slug')),
                'content' => trim($request->postString('content')),
                'is_published' => $request->postBool('is_published'),
            ];
            if ($id) {
                $this->newsService->update($id, $data);
                $msg = $this->translator->trans('admin.news_updated');
            } else {
                $data['author_id'] = $this->session->getUser()->getId();
                $id = $this->newsService->create($data);
                $msg = $id ? $this->translator->trans('admin.news_created') : $this->translator->trans('admin.news_create_failed');
            }
            $this->session->setFlash('success', $msg);
            return new RedirectResponse('/admin/news');
        }
        return new Response(View::render('news/form.tpl', ['news' => $news]));
    }

    public function delete(int $id): Response
    {
        $this->newsService->delete($id);
        $this->session->setFlash('success', $this->translator->trans('admin.news_deleted'));
        return new RedirectResponse('/admin/news');
    }
}
