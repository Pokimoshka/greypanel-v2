<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Core\Database;
use GreyPanel\Interface\Repository\ForumCategoryRepositoryInterface;
use GreyPanel\Interface\Repository\ForumForumRepositoryInterface;
use GreyPanel\Interface\Repository\ForumLikeRepositoryInterface;
use GreyPanel\Interface\Repository\ForumPostRepositoryInterface;
use GreyPanel\Interface\Repository\ForumReadRepositoryInterface;
use GreyPanel\Interface\Repository\ForumThreadRepositoryInterface;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\ForumServiceInterface;
use GreyPanel\Interface\Service\MarkdownServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class ForumService implements ForumServiceInterface
{
    private CacheService $cache;

    public function __construct(
        private ForumCategoryRepositoryInterface $categoryRepo,
        private ForumForumRepositoryInterface $forumRepo,
        private ForumThreadRepositoryInterface $threadRepo,
        private ForumPostRepositoryInterface $postRepo,
        private ForumLikeRepositoryInterface $likeRepo,
        private ForumReadRepositoryInterface $readRepo,
        private UserRepositoryInterface $userRepo,
        private MarkdownServiceInterface $markdown,
        private Database $db,
        private LoggerInterface $logger,
    ) {
        $this->cache = new CacheService('forum_categories');
    }

    public function getCategoriesWithForums(): array
    {
        return $this->cache->get('categories_with_forums', function (ItemInterface $item) {
            $item->expiresAfter(600); // 10 минут
            $categories = $this->categoryRepo->findAll();
            foreach ($categories as &$cat) {
                $cat['forums'] = $this->forumRepo->findByCategoryId($cat['id']);
                foreach ($cat['forums'] as &$forum) {
                    $forum['threads_count'] = $this->threadRepo->countByForumId($forum['id']);
                    $lastThreads = $this->threadRepo->findByForumId($forum['id'], 1, 1);
                    $forum['last_thread'] = $lastThreads[0] ?? null;
                    if ($forum['last_thread']) {
                        $forum['last_post'] = $this->postRepo->findLastByThreadId($forum['last_thread']['id']);
                    }
                }
            }
            return $categories;
        });
    }

    public function getThreadsByForum(int $forumId, int $page, int $perPage = 20): array
    {
        $threads = $this->threadRepo->findByForumId($forumId, $page, $perPage);
        foreach ($threads as &$thread) {
            $thread['author'] = $this->userRepo->findById($thread['user_id']);
            $lastPost = $this->postRepo->findLastByThreadId($thread['id']);
            $thread['last_post_user'] = $lastPost ? $this->userRepo->findById($lastPost['user_id']) : null;
        }
        return $threads;
    }

    public function getThreadsCount(int $forumId): int
    {
        return $this->threadRepo->countByForumId($forumId);
    }

    public function getThread(int $threadId, int $page, int $perPage = 20): ?array
    {
        $thread = $this->threadRepo->findById($threadId);
        if (!$thread) {
            return null;
        }
        $thread['author'] = $this->userRepo->findById($thread['user_id']);
        $thread['posts'] = $this->postRepo->findByThreadId($threadId, $page, $perPage);
        foreach ($thread['posts'] as &$post) {
            $post['author'] = $this->userRepo->findById($post['user_id']);
            $post['content_html'] = $this->markdown->parse($post['content']);
        }
        $thread['posts_count'] = $this->postRepo->countByThreadId($threadId);
        return $thread;
    }

    public function createThread(int $forumId, int $userId, string $title, string $content): int
    {
        $this->db->getPdo()->beginTransaction();
        try {
            $threadId = $this->threadRepo->create($forumId, $userId, $title, $content);

            $user = $this->userRepo->findById($userId);
            if ($user) {
                $user->setCountThread($user->getCountThread() + 1);
                $this->userRepo->update($user);
            }

            $this->db->getPdo()->commit();

            // Сбрасываем кэш главной страницы
            $homeCache = new CacheService('home');
            $homeCache->delete('last_topics');
            $homeCache->delete('top_donators');

            return $threadId;
        } catch (\Throwable $e) {
            $this->db->getPdo()->rollBack();
            $this->logger->error('Failed to create thread: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createPost(int $threadId, int $userId, string $content): int
    {
        $postId = $this->postRepo->create($threadId, $userId, $content);
        $user = $this->userRepo->findById($userId);
        if ($user) {
            $user->setCountPost($user->getCountPost() + 1);
            $this->userRepo->update($user);
        }
        $postsCount = $this->postRepo->countByThreadId($threadId);
        $lastPost = $this->postRepo->findLastByThreadId($threadId);
        $this->threadRepo->updateStats($threadId, $postsCount, $lastPost['created_at']);
        return $postId;
    }

    public function like(int $userId, string $type, int $targetId): bool
    {
        if ($this->likeRepo->hasLiked($userId, $type, $targetId)) {
            return false;
        }
        $this->likeRepo->addLike($userId, $type, $targetId);
        if ($type === 'thread') {
            $thread = $this->threadRepo->findById($targetId);
            if ($thread) {
                $author = $this->userRepo->findById($thread['user_id']);
                if ($author) {
                    $author->setCountLike($author->getCountLike() + 1);
                    $this->userRepo->update($author);
                }
            }
        } elseif ($type === 'post') {
            $post = $this->postRepo->findById($targetId);
            if ($post) {
                $author = $this->userRepo->findById($post['user_id']);
                if ($author) {
                    $author->setCountLike($author->getCountLike() + 1);
                    $this->userRepo->update($author);
                }
            }
        }
        return true;
    }

    public function markThreadRead(int $userId, int $threadId): void
    {
        $this->readRepo->markAsRead($userId, $threadId);
    }

    public function incrementViews(int $threadId): void
    {
        $this->threadRepo->incrementViews($threadId);
    }

    public function search(string $query, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $like = '%' . addcslashes($query, '%_') . '%';
        $prefix = $this->db->getPrefix();
        $sql = "SELECT t.*, f.title as forum_title, u.username as author_name
                FROM {$prefix}forum_threads t
                LEFT JOIN {$prefix}forum_forums f ON t.forum_id = f.id
                LEFT JOIN {$prefix}users u ON t.user_id = u.id
                WHERE (t.title LIKE ? OR t.content LIKE ?) AND t.is_deleted = 0
                ORDER BY t.last_post_at DESC
                LIMIT ? OFFSET ?";
        $threads = $this->db->fetchAll($sql, [$like, $like, $perPage, $offset]);
        foreach ($threads as &$thread) {
            $thread['url'] = "/forum/thread/{$thread['id']}";
        }
        return $threads;
    }

    public function countSearch(string $query): int
    {
        $like = '%' . addcslashes($query, '%_') . '%';
        $prefix = $this->db->getPrefix();
        $sql = "SELECT COUNT(*) as cnt FROM {$prefix}forum_threads
                WHERE (title LIKE ? OR content LIKE ?) AND is_deleted = 0";
        $row = $this->db->fetchOne($sql, [$like, $like]);
        return (int)($row['cnt'] ?? 0);
    }

    public function clearCache(): void
    {
        $this->cache->delete('categories_with_forums');
    }
}
