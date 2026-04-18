<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Service\EncryptionServiceInterface;
use GreyPanel\Service\MonitorService;

class AdminServerSettingsController
{
    public function __construct(
        private MonitorServerRepositoryInterface $repo,
        private MonitorService $monitorService,
        private EncryptionServiceInterface $encryption
    ) {}

    public function index(Request $request): Response
    {
        $servers = $this->repo->findAll();
        return new Response(View::render('server_settings/index.tpl', [
            'servers' => $servers,
        ]));
    }

    public function form(Request $request, ?int $id = null): Response
    {
        if ($id !== null) {
            $server = $this->repo->findById($id);
            if (!$server) {
                return new RedirectResponse('/admin/server-settings');
            }
        } else {
            $server = null;
        }

        if ($request->isPost()) {
            $baseData = [
                'type'       => $request->post('type', 'halflife'),
                'ip'         => trim($request->post('ip')),
                'c_port'     => (int)$request->post('c_port'),
                'q_port'     => (int)$request->post('q_port'),
                's_port'     => (int)$request->post('s_port'),
                'disabled'   => (int)$request->post('disabled', 0),
            ];

            $settings = [
                'privilege_storage'   => (int)$request->post('privilege_storage', 1),
                'stats_engine'        => (int)$request->post('stats_engine', 1),
                'amxbans_db_host'     => $request->post('amxbans_db_host'),
                'amxbans_db_user'     => $request->post('amxbans_db_user'),
                'amxbans_db_name'     => $request->post('amxbans_db_name'),
                'amxbans_db_prefix'   => $request->post('amxbans_db_prefix'),
                'csstats_db_host'     => $request->post('csstats_db_host'),
                'csstats_db_user'     => $request->post('csstats_db_user'),
                'csstats_db_name'     => $request->post('csstats_db_name'),
                'aes_stats_db_host'   => $request->post('aes_stats_db_host'),
                'aes_stats_db_user'   => $request->post('aes_stats_db_user'),
                'aes_stats_db_name'   => $request->post('aes_stats_db_name'),
                'ftp_host'            => $request->post('ftp_host'),
                'ftp_user'            => $request->post('ftp_user'),
                'ftp_path'            => $request->post('ftp_path'),
            ];

            if ($pass = $request->post('amxbans_db_pass')) {
                $settings['amxbans_db_pass'] = $this->encryption->encrypt($pass);
            }
            if ($pass = $request->post('csstats_db_pass')) {
                $settings['csstats_db_pass'] = $this->encryption->encrypt($pass);
            }
            if ($pass = $request->post('aes_stats_db_pass')) {
                $settings['aes_stats_db_pass'] = $this->encryption->encrypt($pass);
            }

            if ($pass = $request->post('ftp_pass')) {
                $settings['ftp_pass'] = $this->encryption->encrypt($pass);
            }

            if ($id !== null) {
                $this->repo->update($id, $baseData);
                $this->repo->updateSettings($id, $settings);
            } else {
                $data = array_merge($baseData, $settings);
                $id = $this->repo->create($data);
            }

            $this->monitorService->updateServerStatus($id);

            $this->monitorService->clearCache();

            return new RedirectResponse('/admin/server-settings');
        }

        return new Response(View::render('server_settings/form.tpl', [
            'server' => $server,
        ]));
    }

    public function delete(Request $request, int $id): Response
    {
        $this->repo->delete($id);
        return new RedirectResponse('/admin/server-settings');
    }

    public function testConnection(Request $request, int $id): Response
    {
        $this->monitorService->updateServerStatus($id);
        return new RedirectResponse('/admin/server-settings');
    }
}