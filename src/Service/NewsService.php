<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Repository\NewsRepositoryInterface;

final class NewsService implements NewsServiceInterface
{
    public function __construct(
        private NewsRepositoryInterface $newsRepo,
        private MarkdownServiceInterface $markdown
    ) {}

    public function getPaginated(int $page, int $perPage, bool $publishedOnly = true): array
    {
        $news = $this->newsRepo->findPaginated($page, $perPage, $publishedOnly);
        foreach ($news as &$item) {
            $item['content'] = $this->markdown->parse($item['content']);
        }
        return $news;
    }

    public function count(bool $publishedOnly = true): int
    {
        return $this->newsRepo->count($publishedOnly);
    }

    public function getBySlug(string $slug): ?array
    {
        $news = $this->newsRepo->findBySlug($slug);
        if ($news) {
            $news['content'] = $this->markdown->parse($news['content']);
        }
        return $news;
    }

    public function getById(int $id): ?array
    {
        $news = $this->newsRepo->findById($id);
        if ($news) {
            $news['content'] = $this->markdown->parse($news['content']);
        }
        return $news;
    }

    public function create(array $data): int
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->slugify($data['title']);
        }
        return $this->newsRepo->create($data);
    }

    public function update(int $id, array $data): void
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->slugify($data['title']);
        }
        $this->newsRepo->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->newsRepo->delete($id);
    }

    public function incrementViews(int $id): void
    {
        $this->newsRepo->incrementViews($id);
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return $text ?: 'n-a';
    }
}