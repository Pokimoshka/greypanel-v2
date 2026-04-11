<?php

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Repository\VipServerRepositoryInterface;
use GreyPanel\Repository\VipPrivilegeRepositoryInterface;
use GreyPanel\Service\VipActivationService;
use GreyPanel\Service\EncryptionServiceInterface;

class AdminVipController
{
    public function __construct(
        private VipServerRepositoryInterface $serverRepo,
        private VipPrivilegeRepositoryInterface $privilegeRepo,
        private VipActivationService $activationService,
        private EncryptionServiceInterface $encryption
    ) {
        $this->serverRepo = $serverRepo;
        $this->privilegeRepo = $privilegeRepo;
        $this->activationService = $activationService;
        $this->encryption = $encryption;
    }

    public function servers(Request $request): Response
    {
        $servers = $this->serverRepo->findAll();
        $html = View::render('vip/servers.tpl', [
            'servers' => $servers,
        ]);
        return new Response($html);
    }

    public function serverForm(Request $request, ?int $id = null): Response
    {
        $server = null;
        if ($id) {
            $server = $this->serverRepo->findById($id);
            if (!$server) {
                return new RedirectResponse('/admin/vip/servers');
            }
        }

        if ($request->isPost()) {
            $data = [
                'type' => (int)$request->post('type'),
                'host' => trim($request->post('host')),
                'user' => trim($request->post('user')),
                'database' => trim($request->post('database')),
                'prefix' => trim($request->post('prefix')),
                'amx_id' => (int)$request->post('amx_id', 0),
                'server_name' => trim($request->post('server_name')),
                'server_ip' => trim($request->post('server_ip')),
                'server_port' => (int)$request->post('server_port'),
            ];
            $password = $request->post('password');
            $data['encrypted_password'] = !empty($password) ? $this->encryption->encrypt($password) : ($server['encrypted_password'] ?? '');

            if ($id) {
                $this->serverRepo->update($id, $data);
            } else {
                $id = $this->serverRepo->create($data);
            }
            return new RedirectResponse('/admin/vip/servers');
        }

        $html = View::render('vip/server_form.tpl', [
            'server' => $server,
        ]);
        return new Response($html);
    }

    public function serverDelete(Request $request, int $id): Response
    {
        $this->serverRepo->delete($id);
        return new RedirectResponse('/admin/vip/servers');
    }

    public function privileges(Request $request, int $serverId): Response
    {
        $server = $this->serverRepo->findById($serverId);
        if (!$server) {
            return new RedirectResponse('/admin/vip/servers');
        }
        $privileges = $this->privilegeRepo->getByServerId($serverId);

        $html = View::render('vip/privileges.tpl', [
            'server' => $server,
            'privileges' => $privileges,
        ]);
        return new Response($html);
    }

    public function privilegeForm(Request $request, int $serverId, ?int $privId = null): Response
    {
        $server = $this->serverRepo->findById($serverId);
        if (!$server) {
            return new RedirectResponse('/admin/vip/servers');
        }

        $privilege = null;
        if ($privId) {
            $privilege = $this->privilegeRepo->findById($privId);
            if (!$privilege || $privilege['server_id'] != $serverId) {
                return new RedirectResponse("/admin/vip/servers/{$serverId}/privileges");
            }
        }

        if ($request->isPost()) {
            $title = trim($request->post('title'));
            $flags = trim($request->post('flags'));
            $price = (int)$request->post('price_per_day');

            if ($privId) {
                $this->privilegeRepo->update($privId, $title, $flags, $price);
            } else {
                $this->privilegeRepo->create($serverId, $title, $flags, $price);
            }
            return new RedirectResponse("/admin/vip/servers/{$serverId}/privileges");
        }

        $html = View::render('vip/privilege_form.tpl', [
            'server' => $server,
            'privilege' => $privilege,
        ]);
        return new Response($html);
    }

    public function privilegeDelete(Request $request, int $serverId, int $privId): Response
    {
        $this->privilegeRepo->delete($privId);
        return new RedirectResponse("/admin/vip/servers/{$serverId}/privileges");
    }

    public function testConnection(Request $request, int $id): Response
    {
        $server = $this->serverRepo->findById($id);
        if (!$server) {
            return new JsonResponse(['success' => false, 'message' => 'Сервер не найден']);
        }

        $result = $this->activationService->testConnection($server);
        return new JsonResponse($result);
    }
}