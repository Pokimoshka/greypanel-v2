<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Repository\ForumForumRepositoryInterface;
use GreyPanel\Interface\Repository\ForumPostRepositoryInterface;
use GreyPanel\Interface\Repository\ForumThreadRepositoryInterface;
use GreyPanel\Interface\Service\ForumServiceInterface;
use GreyPanel\Interface\Service\PermissionServiceInterface;
use GreyPanel\Interface\Service\SeoServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ForumController extends AbstractController
{
    public function __construct(
        private ForumServiceInterface $forumService,
        private ForumForumRepositoryInterface $forumRepo,
        private ForumThreadRepositoryInterface $threadRepo,
        private ForumPostRepositoryInterface $postRepo,
        private SessionServiceInterface $session,
        private SeoServiceInterface $seoService,
        private PermissionServiceInterface $permissionService,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        parent::__construct($serializer, $validator, $translator);
    }

    public function index(Request $request): Response
    {
        $categories = $this->forumService->getCategoriesWithForums();
        $meta = $this->seoService->getMetaTags('Форум', 'Разделы форума GreyPanel');
        return new Response(View::render('forum/index.tpl', [
            'categories' => $categories,
            'meta_title' => $meta['title'],
            'meta_description' => $meta['description'],
            'meta_keywords' => $meta['keywords'],
        ]));
    }

    public function forum(Request $request, int $id): Response
    {
        $forum = $this->forumRepo->findById($id);
        if (!$forum) {
            return new RedirectResponse('/forum');
        }

        $page = (int)$request->get('page', 1);
        $perPage = 20;
        $threads = $this->forumService->getThreadsByForum($id, $page, $perPage);
        $total = $this->forumService->getThreadsCount($id);
        $meta = $this->seoService->getMetaTags($forum['title'], $forum['description']);

        return new Response(View::render('forum/forum.tpl', [
            'forum' => $forum,
            'threads' => $threads,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'meta_title' => $meta['title'],
            'meta_description' => $meta['description'],
            'meta_keywords' => $meta['keywords'],
        ]));
    }

    public function thread(Request $request, int $id): Response
    {
        $page = (int)$request->get('page', 1);
        $perPage = 15;

        $thread = $this->forumService->getThread($id, $page, $perPage);
        if (!$thread) {
            return new RedirectResponse('/forum');
        }

        $this->forumService->incrementViews($id);
        if ($this->session->isLoggedIn()) {
            $this->forumService->markThreadRead($this->session->getUser()?->getId(), $id);
        }

        $meta = $this->seoService->getMetaTags($thread['title'], mb_substr(strip_tags($thread['content']), 0, 150));

        return new Response(View::render('forum/thread.tpl', [
            'thread' => $thread,
            'page' => $page,
            'per_page' => $perPage,
            'meta_title' => $meta['title'],
            'meta_description' => $meta['description'],
            'meta_keywords' => $meta['keywords'],
        ]));
    }

    public function createThreadForm(Request $request, int $forumId): Response
    {
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

        $forumId = $request->postInt('forum_id');
        $title = $request->postString('title');
        $content = $request->postString('content');
        $userId = $this->session->getUser()?->getId();

        if (empty($title) || empty($content)) {
            return new RedirectResponse("/forum/forum/{$forumId}/create");
        }

        $threadId = $this->forumService->createThread($forumId, $userId, $title, $content);
        return new RedirectResponse("/forum/thread/{$threadId}");
    }

    public function createPost(Request $request): JsonResponse
    {
        if (!$request->isPost()) {
            return $this->json(['error' => 'Invalid method'], 405);
        }

        $threadId = $request->postInt('thread_id');
        $content = trim($request->postString('content'));
        $userId = $this->session->getUser()?->getId();

        if (empty($content)) {
            return $this->json(['error' => $this->translator->trans('forum.content_empty')]);
        }

        $postId = $this->forumService->createPost($threadId, $userId, $content);
        return $this->json(['success' => true, 'post_id' => $postId]);
    }

    public function like(Request $request): JsonResponse
    {
        if (!$request->isPost()) {
            return $this->json(['error' => 'Invalid method'], 405);
        }

        $type = $request->postString('type');
        $targetId = $request->postInt('target_id');
        $userId = $this->session->getUser()?->getId();

        $result = $this->forumService->like($userId, $type, $targetId);
        if ($result) {
            return $this->json(['success' => true]);
        }
        return $this->json(['error' => 'Already liked']);
    }

    public function editThreadForm(Request $request, int $id): Response
    {
        $thread = $this->threadRepo->findById($id);
        if (!$thread) {
            return new RedirectResponse('/forum');
        }
        if ($thread['user_id'] != $this->session->getUser()?->getId() && !$this->permissionService->hasPermission('c')) {
            return new RedirectResponse('/forum');
        }
        return new Response(View::render('forum/edit_thread.tpl', ['thread' => $thread]));
    }

    public function editThread(Request $request, int $id): Response
    {
        $thread = $this->threadRepo->findById($id);
        if (!$thread) {
            return new RedirectResponse('/forum');
        }
        if ($thread['user_id'] != $this->session->getUser()?->getId() && !$this->permissionService->hasPermission('c')) {
            return new RedirectResponse('/forum');
        }

        if ($request->isPost()) {
            $title = trim($request->postString('title'));
            $content = trim($request->postString('content'));
            if (!empty($title) && !empty($content)) {
                $this->threadRepo->update($id, $title, $content);
            }
            return new RedirectResponse("/forum/thread/{$id}");
        }

        return new Response(View::render('forum/edit_thread.tpl', ['thread' => $thread]));
    }

    public function deleteThread(Request $request, int $id): Response
    {
        $thread = $this->threadRepo->findById($id);
        if (!$thread) {
            return new RedirectResponse('/forum');
        }
        if ($thread['user_id'] != $this->session->getUser()?->getId() && !$this->permissionService->hasPermission('c')) {
            return new RedirectResponse('/forum');
        }
        $this->threadRepo->deleteSoft($id);
        return new RedirectResponse("/forum/forum/{$thread['forum_id']}");
    }

    public function editPostForm(Request $request, int $id): Response
    {
        $post = $this->postRepo->findById($id);
        if (!$post) {
            return new RedirectResponse('/forum');
        }
        $thread = $this->threadRepo->findById($post['thread_id']);
        if ($thread['user_id'] != $this->session->getUser()?->getId() && !$this->permissionService->hasPermission('c')) {
            return new RedirectResponse('/forum');
        }
        return new Response(View::render('forum/edit_post.tpl', ['post' => $post, 'thread' => $thread]));
    }

    public function editPost(Request $request, int $id): Response
    {
        $post = $this->postRepo->findById($id);
        if (!$post) {
            return new RedirectResponse('/forum');
        }
        $thread = $this->threadRepo->findById($post['thread_id']);
        if ($thread['user_id'] != $this->session->getUser()?->getId() && !$this->permissionService->hasPermission('c')) {
            return new RedirectResponse('/forum');
        }

        if ($request->isPost()) {
            $content = trim($request->postString('content'));
            if (!empty($content)) {
                $this->postRepo->update($id, $content);
            }
            return new RedirectResponse("/forum/thread/{$thread['id']}");
        }

        return new Response(View::render('forum/edit_post.tpl', ['post' => $post, 'thread' => $thread]));
    }

    public function deletePost(Request $request, int $id): Response
    {
        $post = $this->postRepo->findById($id);
        if (!$post) {
            return new RedirectResponse('/forum');
        }
        $thread = $this->threadRepo->findById($post['thread_id']);
        if ($thread['user_id'] != $this->session->getUser()?->getId() && !$this->permissionService->hasPermission('c')) {
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
        $query = trim($request->getString('q', ''));
        $page = $request->getInt('page', 1);
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

    public function lastTopics(Request $request): JsonResponse
    {
        $cache = new FilesystemAdapter('widget_forum', 0, ROOT_DIR . '/var/cache');
        $topics = $cache->get('last_topics', function (ItemInterface $item) use ($request) {
            $item->expiresAfter(60);
            $limit = ($request->getInt('limit'));
            return $this->threadRepo->findLast($limit);
        });

        return $this->json($topics);
    }
}
