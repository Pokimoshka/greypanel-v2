<?php

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Repository\ForumCategoryRepositoryInterface;
use GreyPanel\Repository\ForumForumRepositoryInterface;

class AdminForumController
{
    public function __construct(
        private ForumCategoryRepositoryInterface $categoryRepo,
        private ForumForumRepositoryInterface $forumRepo
    ) {
        $this->categoryRepo = $categoryRepo;
        $this->forumRepo = $forumRepo;
    }

    public function categories(Request $request): Response
    {
        $categories = $this->categoryRepo->findAll();
        $html = View::render('forum/categories.tpl', [
            'categories' => $categories,
        ]);
        return new Response($html);
    }

    public function categoryForm(Request $request, $id = null): Response
    {
        $category = null;
        if ($id !== null && is_numeric($id)) {
            $id = (int)$id;
        } else {
            $id = null;
        }

        if ($id !== null) {
            $category = $this->categoryRepo->findById($id);
            if (!$category) {
                return new RedirectResponse('/admin/forum/categories');
            }
        }

        if ($request->isPost()) {
            $title = trim($request->post('title'));
            $description = trim($request->post('description'));
            $sortOrder = (int)$request->post('sort_order', 0);
            if ($id !== null) {
                $this->categoryRepo->update($id, $title, $description, $sortOrder);
            } else {
                $this->categoryRepo->create($title, $description, $sortOrder);
            }
            return new RedirectResponse('/admin/forum/categories');
        }

        $html = View::render('forum/category_form.tpl', [
            'category' => $category,
        ]);
        return new Response($html);
    }

    public function categoryDelete(Request $request, int $id): Response
    {
        $this->categoryRepo->delete($id);
        return new RedirectResponse('/admin/forum/categories');
    }

    public function sortCategories(Request $request): Response
    {
        $order = $request->post('order');
        if (is_array($order)) {
            foreach ($order as $id => $pos) {
                $this->categoryRepo->updateSortOrder((int)$id, (int)$pos);
            }
        }
        return new Response('OK');
    }

    public function forums(Request $request, $categoryId): Response
    {
        if ($categoryId !== null && is_numeric($categoryId)) {
            $categoryId = (int)$categoryId;
        } else {
            $categoryId = null;
        }

        $category = $this->categoryRepo->findById($categoryId);
        if (!$category) return new RedirectResponse('/admin/forum/categories');
        $forums = $this->forumRepo->findByCategoryId($categoryId);
        $html = View::render('forum/forums.tpl', [
            'category' => $category,
            'forums' => $forums,
        ]);
        return new Response($html);
    }

    public function forumForm(Request $request, $categoryId, $id = null): Response
    {
        if ($categoryId !== null && is_numeric($categoryId)) {
            $categoryId = (int)$categoryId;
        } else {
            $categoryId = null;
        }

        $category = $this->categoryRepo->findById($categoryId);
        if (!$category) return new RedirectResponse('/admin/forum/categories');
        $forum = null;

        if ($id !== null && is_numeric($id)) {
            $id = (int)$id;
        } else {
            $id = null;
        }

        if ($id) {
            $forum = $this->forumRepo->findById($id);
            if (!$forum || $forum['category_id'] != $categoryId) {
                return new RedirectResponse("/admin/forum/categories/{$categoryId}/forums");
            }
        }

        if ($request->isPost()) {
            $title = trim($request->post('title'));
            $description = trim($request->post('description'));
            $icon = trim($request->post('icon', 'fa fa-comments'));
            $sortOrder = (int)$request->post('sort_order', 0);
            if ($id) {
                $this->forumRepo->update($id, $categoryId, $title, $description, $icon, $sortOrder);
            } else {
                $this->forumRepo->create($categoryId, $title, $description, $icon, $sortOrder);
            }
            return new RedirectResponse("/admin/forum/categories/{$categoryId}/forums");
        }

        $html = View::render('forum/forum_form.tpl', [
            'category' => $category,
            'forum' => $forum,
        ]);
        return new Response($html);
    }

    public function forumDelete(Request $request, $categoryId, $id): Response
    {
        $this->forumRepo->delete($id);
        return new RedirectResponse("/admin/forum/categories/{$categoryId}/forums");
    }

    public function sortForums(Request $request): Response
    {
        $order = $request->post('order');
        if (is_array($order)) {
            foreach ($order as $id => $pos) {
                $this->forumRepo->updateSortOrder((int)$id, (int)$pos);
            }
        }
        return new Response('OK');
    }
}