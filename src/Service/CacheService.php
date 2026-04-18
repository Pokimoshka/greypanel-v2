<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class CacheService
{
    private FilesystemAdapter $cache;

    public function __construct(string $namespace = 'default')
    {
        $this->cache = new FilesystemAdapter($namespace, 0, __DIR__ . '/../../var/cache');
    }

    public function get(string $key, callable $callback, int $ttl = 3600)
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($callback, $ttl) {
            $item->expiresAfter($ttl);
            return $callback();
        });
    }

    public function delete(string $key): void
    {
        $this->cache->delete($key);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }
}