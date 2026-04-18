<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Repository\MonitorServerRepositoryInterface;
use xPaw\SourceQuery\SourceQuery;
use xPaw\SourceQuery\Exception\SourceQueryException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class MonitorService implements MonitorServiceInterface
{
    private MonitorServerRepositoryInterface $repo;
    private FilesystemAdapter $cache;

    public function __construct(MonitorServerRepositoryInterface $repo)
    {
        $this->repo = $repo;
        $this->cache = new FilesystemAdapter('monitor', 0, __DIR__ . '/../../var/cache');
    }

    public function getServers(): array
    {
        return $this->cache->get('servers_list', function (ItemInterface $item) {
            $item->expiresAfter(30); // 30 секунд – свежие данные
            $servers = $this->repo->findEnabled();
            $result = [];
            foreach ($servers as $server) {
                $result[] = $this->formatServer($server);
            }
            return $result;
        });
    }

    public function updateServerStatus(int $id): void
    {
        $server = $this->repo->findById($id);
        if (!$server || $server['disabled']) {
            return;
        }

        $query = new SourceQuery();
        $status = 1;
        $cache = '';

        try {
            $engine = ($server['type'] === 'halflife') ? SourceQuery::GOLDSOURCE : SourceQuery::SOURCE;
            $query->Connect($server['ip'], (int)$server['c_port'], 1, $engine);
            $info = $query->GetInfo();
            $query->Disconnect();

            $status = 0;
            $cache = serialize($info);
        } catch (SourceQueryException $e) {

        }

        $this->repo->updateStatus($id, $status, $cache, time());
    }

    public function updateAllServers(): void
    {
        $servers = $this->repo->findAll();
        foreach ($servers as $server) {
            if (time() - $server['cache_time'] > 300) {
                $this->updateServerStatus($server['id']);
            }
        }
    }

    private function formatServer(array $server): array
    {
        $cache = unserialize($server['cache']);
        $online = ($server['status'] == 0);

        if ($online && is_array($cache)) {
            $serverName = $cache['HostName'] ?? $server['ip'];
            $map = $cache['Map'] ?? 'unknown';
            $players = ($cache['Players'] ?? 0) . '/' . ($cache['MaxPlayers'] ?? 0);
            $statusHtml = '<span class="badge bg-success">ON</span>';
        } else {
            $serverName = $server['ip'];
            $map = '—';
            $players = '0/0';
            $statusHtml = '<span class="badge bg-danger">OFF</span>';
        }

        return [
            'id' => $server['id'],
            'type' => $server['type'],
            'address' => $server['ip'] . ':' . $server['c_port'],
            'server_name' => htmlspecialchars($serverName),
            'map' => htmlspecialchars($map),
            'players' => $players,
            'status_html' => $statusHtml,
        ];
    }

    public function clearCache(): void
    {
        $this->cache->delete('servers_list');
    }
}