<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Service\MonitorService;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminServerSettingsController
{
    public function __construct(
        private MonitorServerRepositoryInterface $repo,
        private MonitorService $monitorService,
        private SessionServiceInterface $session,
        private EncryptionServiceInterface $encryption,
        private TranslatorInterface $translator
    ) {
    }

    public function index(Request $request): Response
    {
        $servers = $this->repo->findAll();
        return new Response(View::render('server_settings/index.tpl', [
            'servers' => $servers,
        ]));
    }

    public function form(Request $request, ?int $id = null): Response
    {
        $server = $id !== null ? $this->repo->findById($id) : null;

        if (!$server && $id !== null) {
            return new RedirectResponse('/admin/server-settings');
        }

        if ($request->isPost()) {
            $baseData = [
                'type'     => $request->postString('type', 'halflife'),
                'ip'       => $request->postString('ip'),
                'c_port'   => $request->postInt('c_port'),
                'q_port'   => $request->postInt('q_port'),
                's_port'   => $request->postInt('s_port'),
                'disabled' => $request->postInt('disabled', 0),
            ];

            $settings = [
                'privilege_storage'   => $request->postInt('privilege_storage', 1),
                'stats_engine'        => $request->postInt('stats_engine', 1),
                'banlist_db_host'     => $request->postString('banlist_db_host'),
                'banlist_db_user'     => $request->postString('banlist_db_user'),
                'banlist_db_name'     => $request->postString('banlist_db_name'),
                'banlist_db_prefix'   => $request->postString('banlist_db_prefix'),
                'csstats_db_host'     => $request->postString('csstats_db_host'),
                'csstats_db_user'     => $request->postString('csstats_db_user'),
                'csstats_db_name'     => $request->postString('csstats_db_name'),
                'aes_stats_db_host'   => $request->postString('aes_stats_db_host'),
                'aes_stats_db_user'   => $request->postString('aes_stats_db_user'),
                'aes_stats_db_name'   => $request->postString('aes_stats_db_name'),
                'ftp_host'            => $request->postString('ftp_host'),
                'ftp_user'            => $request->postString('ftp_user'),
                'ftp_path'            => $request->postString('ftp_path'),
            ];

            $amxPrefix = trim($request->postString('banlist_db_prefix', ''));
            if ($amxPrefix !== '' && !str_ends_with($amxPrefix, '_')) {
                $amxPrefix .= '_';
            }
            $settings['banlist_db_prefix'] = $amxPrefix;

            if ($pass = $request->postString('banlist_db_pass')) {
                $settings['banlist_db_pass'] = $this->encryption->encrypt($pass);
            }
            if ($pass = $request->postString('csstats_db_pass')) {
                $settings['csstats_db_pass'] = $this->encryption->encrypt($pass);
            }
            if ($pass = $request->postString('aes_stats_db_pass')) {
                $settings['aes_stats_db_pass'] = $this->encryption->encrypt($pass);
            }
            if ($pass = $request->postString('ftp_pass')) {
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
            $this->session->setFlash('success', $this->translator->trans('admin.server_saved'));

            return new RedirectResponse('/admin/server-settings');
        }
        $serverData = null;
        if ($server !== null) {
            $serverData = $server;

            $sensitive = [
                'banlist_db_pass',
                'csstats_db_pass',
                'aes_stats_db_pass',
                'ftp_pass',
                'password',
                'secret',
            ];

            foreach ($sensitive as $field) {
                if (array_key_exists($field, $serverData)) {
                    $serverData[$field] = '';
                }
            }
        }

        return new Response(View::render('server_settings/form.tpl', [
            'server' => $serverData,
            'id'     => $id,
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
