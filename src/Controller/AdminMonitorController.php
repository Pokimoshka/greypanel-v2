<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Service\MonitorService;

class AdminMonitorController
{
    public function __construct(
        private MonitorServerRepositoryInterface $repo,
        private MonitorService $service
    ) {}

    public function servers(Request $request): Response
    {
        $servers = $this->repo->findAll();
        return new Response(View::render('monitor/servers.tpl', ['servers' => $servers]));
    }

    public function serverForm(Request $request, $id = null): Response
    {
        $server = null;
        if ($id !== null && is_numeric($id)) {
            $id = (int)$id;
        } else {
            $id = null;
        }
        if ($id !== null) {
            $server = $this->repo->findById($id);
            if (!$server) {
                return new RedirectResponse('/admin/monitor/servers');
            }
        }

        if ($request->isPost()) {
            $data = [
                'type' => $request->post('type'),
                'ip' => trim($request->post('ip')),
                'c_port' => (int)$request->post('c_port'),
                'q_port' => (int)$request->post('q_port'),
                's_port' => (int)$request->post('s_port'),
                'disabled' => (int)$request->post('disabled', 0),
            ];

            if ($id !== null) {
                $this->repo->update($id, $data);
            } else {
                $id = $this->repo->create($data);
            }

            $this->service->updateServerStatus($id);
            return new RedirectResponse('/admin/monitor/servers');
        }

        return new Response(View::render('monitor/server_form.tpl', ['server' => $server]));
    }

    public function serverDelete(Request $request, int $id): Response
    {
        $this->repo->delete($id);
        return new RedirectResponse('/admin/monitor/servers');
    }

    public function refresh(Request $request, int $id): Response
    {
        $this->service->updateServerStatus($id);
        return new RedirectResponse('/admin/monitor/servers');
    }
}