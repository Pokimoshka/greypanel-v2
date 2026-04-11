<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\JsonResponse;
use GreyPanel\Service\MonitorService;

class MonitorController
{
    private MonitorService $monitorService;

    public function __construct(MonitorService $monitorService)
    {
        $this->monitorService = $monitorService;
    }

    public function index(Request $request): Response
    {
        return new Response(View::render('monitor/index.tpl'));
    }

    public function data(Request $request): JsonResponse
    {
        $servers = $this->monitorService->getServers();
        return new JsonResponse($servers);
    }
}