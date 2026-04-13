<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\JsonResponse;
use GreyPanel\Service\ForumServiceInterface;
use GreyPanel\Repository\ForumForumRepositoryInterface;
use GreyPanel\Repository\ForumThreadRepositoryInterface;
use GreyPanel\Repository\ForumPostRepositoryInterface;
use GreyPanel\Service\SessionServiceInterface;

final class ForumController
{
    public function __construct(
        private ForumServiceInterface $forumService,
        private ForumForumRepositoryInterface $forumRepo,
        private ForumThreadRepositoryInterface $threadRepo,
        private ForumPostRepositoryInterface $postRepo,
        private SessionServiceInterface $session
    ) {}

    public function index(Request $request): Response
    {
        $categories = $this->forumService->getCategoriesWithForums();
        return new Response(View::render('forum/index.tpl', ['categories' => $categories]));
    }

    public function forum(Request $request, $id): Response
    {
        if ($id !== null && is_numeric($id)) {
            $id = (int)$id;
        } else {
            $id = null;
        }

        $forum = $this->forumRepo->findById($id);
        if (!$forum) {
            return new RedirectResponse('/forum');
        }

        $page = (int)$request->get('page', 1);
        $perPage = 20;
        $threads = $this->forumService->getThreadsByForum($id, $page, $perPage);
        $total = $this->forumService->getThreadsCount($id);

        return new Response(View::render('forum/forum.tpl', [
            'forum' => $forum,
            'threads' => $threads,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ]));
    }

    public function thread(Request $request, $id): Response
    {
        if ($id !== null && is_numeric($id)) {
            $id = (int)$id;
        } else {
            $id = null;
        }

        $page = (int)$request->get('page', 1);
        $perPage = 15;

        $thread = $this->forumService->getThread($id, $page, $perPage);
        if (!$thread) {
            return new RedirectResponse('/forum');
        }

        $this->forumService->incrementViews($id);
        if ($this->session->isLoggedIn()) {
            $this->forumService->markThreadRead($this->session->getUserId(), $id);
        }

        return new Response(View::render('forum/thread.tpl', [
            'thread' => $thread,
            'page' => $page,
            'per_page' => $perPage,
        ]));
    }

    public function createThreadForm(Request $request, $forumId): Response
    {
        if ($forumId !== null && is_numeric($forumId)) {
            $forumId = (int)$forumId;
        } else {
            $forumId = null;
        }

        $forum = $this->forumRepo->findById($forumId);
        if (!$forum) {
            return new RedirectResponse('/forum');
        }
        return new Response(View::render('forum/create_thread.tpl', ['forum' => $forum]));
    }

    public function createThread(Request $request): Response
    {
        if (!$request->isPost()) {
            return new RedirectResponse('/forum');
        }

        $forumId = (int)$request->post('forum_id');
        $title = trim($request->post('title'));
        $content = trim($request->post('content'));
        $content = strip_tags($content);
        $userId = $this->session->getUserId();

        if (empty($title) || empty($content)) {
            return new RedirectResponse("/forum/forum/{$forumId}/create");
        }

        $threadId = $this->forumService->createThread($forumId, $userId, $title, $content);
        return new RedirectResponse("/forum/thread/{$threadId}");
    }

    public function createPost(Request $request): JsonResponse
    {
        if (!$request->isPost()) {
            return new JsonResponse(['error' => 'Invalid method'], 405);
        }

        $threadId = (int)$request->post('thread_id');
        $content = trim($request->post('content'));
        $content = strip_tags($content);
        $userId = $this->session->getUserId();

        if (empty($content)) {
            return new JsonResponse(['error' => 'Content is empty']);
        }

        $postId = $this->forumService->createPost($threadId, $userId, $content);
        return new JsonResponse(['success' => true, 'post_id' => $postId]);
    }

    public function like(Request $request): JsonResponse
    {
        if (!$request->isPost()) {
            return new JsonResponse(['error' => 'Invalid method'], 405);
        }

        $type = $request->post('type');
        $targetId = (int)$request->post('target_id');
        $userId = $this->session->getUserId();

        $result = $this->forumService->like($userId, $type, $targetId);
        if ($result) {
            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['error' => 'Already liked']);
    }

    public function editThreadForm(Request $request, int $id): Response
    {
        $thread = $this->threadRepo->findById($id);
        if (!$thread) {
            return new RedirectResponse('/forum');
        }
        if ($thread['user_id'] != $this->session->getUserId() && $this->session->getUserGroup() < 3) {
            return new RedirectResponse('/forum');
        }
        return new Response(View::render('forum/edit_thread.tpl', ['thread' => $thread]));
    }

    public function editThread(Request $request, $id): Response
    {
        if ($id !== null && is_numeric($id)) {
            $id = (int)$id;
        } else {
            $id = null;
        }

        $thread = $this->threadRepo->findById($id);
        if (!$thread) {
            return new RedirectResponse('/forum');
        }
        if ($thread['user_id'] != $this->session->getUserId() && $this->session->getUserGroup() < 3) {
            return new RedirectResponse('/forum');
        }

        if ($request->isPost()) {
            $title = trim($request->post('title'));
            $content = trim($request->post('content'));
            $content = strip_tags($content);
            if (!empty($title) && !empty($content)) {
                $this->threadRepo->update($id, $title, $content);
            }
            return new RedirectResponse("/forum/thread/{$id}");
        }

        return new Response(View::render('forum/edit_thread.tpl', ['thread' => $thread]));
    }

    public function deleteThread(Request $request, $id): Response
    {
        if ($id !== null && is_numeric($id)) {
            $id = (int)$id;
        } else {
            $id = null;
        }

        $thread = $this->threadRepo->findById($id);
        if (!$thread) {
            return new RedirectResponse('/forum');
        }
        if ($thread['user_id'] != $this->session->getUserId() && $this->session->getUserGroup() < 3) {
            return new RedirectResponse('/forum');
        }
        $this->threadRepo->deleteSoft($id);
        return new RedirectResponse("/forum/forum/{$thread['forum_id']}");
    }

    public function editPostForm(Request $request, $id): Response
    {
        if ($id !== null && is_numeric($id)) {
            $id = (int)$id;
        } else {
            $id = null;
        }

        $post = $this->postRepo->findById($id);
        if (!$post) {
            return new RedirectResponse('/forum');
        }
        $thread = $this->threadRepo->findById($post['thread_id']);
        if ($post['user_id'] != $this->session->getUserId() && $this->session->getUserGroup() < 3) {
            return new RedirectResponse('/forum');
        }
        return new Response(View::render('forum/edit_post.tpl', ['post' => $post, 'thread' => $thread]));
    }

    public function editPost(Request $request, $id): Response
    {
        if ($id !== null && is_numeric($id)) {
            $id = (int)$id;
        } else {
            $id = null;
        }

        $post = $this->postRepo->findById($id);
        if (!$post) {
            return new RedirectResponse('/forum');
        }
        $thread = $this->threadRepo->findById($post['thread_id']);
        if ($post['user_id'] != $this->session->getUserId() && $this->session->getUserGroup() < 3) {
            return new RedirectResponse('/forum');
        }

        if ($request->isPost()) {
            $content = trim($request->post('content'));
            $content = strip_tags($content);
            if (!empty($content)) {
                $this->postRepo->update($id, $content);
            }
            return new RedirectResponse("/forum/thread/{$thread['id']}");
        }

        return new Response(View::render('forum/edit_post.tpl', ['post' => $post, 'thread' => $thread]));
    }

    public function deletePost(Request $request, $id): Response
    {
        if ($id !== null && is_numeric($id)) {
            $id = (int)$id;
        } else {
            $id = null;
        }

        $post = $this->postRepo->findById($id);
        if (!$post) {
            return new RedirectResponse('/forum');
        }
        $thread = $this->threadRepo->findById($post['thread_id']);
        if ($post['user_id'] != $this->session->getUserId() && $this->session->getUserGroup() < 3) {
            return new RedirectResponse('/forum');
        }
        $this->postRepo->delete($id);

        $postsCount = $this->postRepo->countByThreadId($thread['id']);
        $lastPost = $this->postRepo->findLastByThreadId($thread['id']);
        $lastPostAt = $lastPost ? $lastPost['created_at'] : $thread['created_at'];
        $this->threadRepo->updateStats($thread['id'], $postsCount, $lastPostAt);

        return new RedirectResponse("/forum/thread/{$thread['id']}");
    }

    public function search(Request $request): Response
    {
        $query = trim($request->get('q', ''));
        $page = (int)$request->get('page', 1);
        $perPage = 20;

        $results = [];
        $total = 0;
        if (strlen($query) >= 3) {
            $results = $this->forumService->search($query, $page, $perPage);
            $total = $this->forumService->countSearch($query);
        }

        return new Response(View::render('forum/search.tpl', [
            'query' => $query,
            'results' => $results,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ]));
    }
}