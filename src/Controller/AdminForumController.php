<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Repository\ForumCategoryRepositoryInterface;
use GreyPanel\Interface\Repository\ForumForumRepositoryInterface;
use GreyPanel\Interface\Service\ForumServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminForumController
{
    public function __construct(
        private ForumCategoryRepositoryInterface $categoryRepo,
        private ForumForumRepositoryInterface $forumRepo,
        private ForumServiceInterface $forumService,
        private SessionServiceInterface $session,
        private TranslatorInterface $translator
    ) {
    }

    public function categories(Request $request): Response
    {
        $categories = $this->categoryRepo->findAll();
        $html = View::render('forum/categories.tpl', [
            'categories' => $categories,
        ]);
        return new Response($html);
    }

    public function categoryForm(Request $request, ?int $id = null): Response
    {
        $category = null;
        if ($id !== null) {
            $category = $this->categoryRepo->findById($id);
            if (!$category) {
                return new RedirectResponse('/admin/forum/categories');
            }
        }

        if ($request->isPost()) {
            $title = $request->postString('title');
            $description = $request->postString('description');
            $sortOrder = $request->postInt('sort_order', 0);

            if ($id !== null) {
                $this->categoryRepo->update($id, $title, $description, $sortOrder);
            } else {
                $this->categoryRepo->create($title, $description, $sortOrder);
            }

            $this->session->setFlash('success', $this->translator->trans('admin.category_saved'));
            return new RedirectResponse('/admin/forum/categories');
        }

        $this->forumService->clearCache();

        $html = View::render('forum/category_form.tpl', [
            'category' => $category,
        ]);
        return new Response($html);
    }

    public function categoryDelete(Request $request, int $id): Response
    {
        $this->categoryRepo->delete($id);
        $this->forumService->clearCache();
        $this->session->setFlash('success', $this->translator->trans('admin.category_deleted'));
        return new RedirectResponse('/admin/forum/categories');
    }

    public function sortCategories(Request $request): Response
    {
        $rawOrder = $request->post('order');
        if (is_string($rawOrder)) {
            $order = json_decode($rawOrder, true);
        } else {
            $order = $rawOrder;
        }

        if (is_array($order)) {
            foreach ($order as $id => $pos) {
                $this->categoryRepo->updateSortOrder((int)$id, (int)$pos);
            }
        }
        $this->forumService->clearCache();
        return new Response('OK');
    }

    public function forums(Request $request, int $categoryId): Response
    {
        $category = $this->categoryRepo->findById($categoryId);
        if (!$category) {
            return new RedirectResponse('/admin/forum/categories');
        }
        $forums = $this->forumRepo->findByCategoryId($categoryId);
        $html = View::render('forum/forums.tpl', [
            'category' => $category,
            'forums' => $forums,
        ]);
        return new Response($html);
    }

    public function forumForm(Request $request, int $categoryId, ?int $id = null): Response
    {
        $category = $this->categoryRepo->findById($categoryId);
        if (!$category) {
            return new RedirectResponse('/admin/forum/categories');
        }

        $forum = null;

        if ($id) {
            $forum = $this->forumRepo->findById($id);
            if (!$forum || $forum['category_id'] != $categoryId) {
                return new RedirectResponse("/admin/forum/categories/{$categoryId}/forums");
            }
        }

        if ($request->isPost()) {
            $title = trim($request->postString('title'));
            $description = trim($request->postString('description'));
            $icon = trim($request->postString('icon', 'fa fa-comments'));
            $sortOrder = $request->postInt('sort_order', 0);
            if ($id) {
                $this->forumRepo->update($id, $categoryId, $title, $description, $icon, $sortOrder);
            } else {
                $this->forumRepo->create($categoryId, $title, $description, $icon, $sortOrder);
            }
            $this->session->setFlash('success', $this->translator->trans('admin.forum_saved'));
            return new RedirectResponse("/admin/forum/categories/{$categoryId}/forums");
        }

        $this->forumService->clearCache();

        $html = View::render('forum/forum_form.tpl', [
            'category' => $category,
            'forum' => $forum,
        ]);
        return new Response($html);
    }

    public function forumDelete(Request $request, int $categoryId, int $id): Response
    {
        $id = (int) $id;
        $this->forumRepo->delete($id);
        $this->forumService->clearCache();
        $this->session->setFlash('success', $this->translator->trans('admin.forum_deleted'));
        return new RedirectResponse("/admin/forum/categories/{$categoryId}/forums");
    }

    public function sortForums(Request $request): Response
    {
        $rawOrder = $request->post('order');
        if (is_string($rawOrder)) {
            $order = json_decode($rawOrder, true);
        } else {
            $order = $rawOrder;
        }

        if (is_array($order)) {
            foreach ($order as $id => $pos) {
                $this->forumRepo->updateSortOrder((int)$id, (int)$pos);
            }
        }
        $this->forumService->clearCache();
        return new Response('OK');
    }
}
