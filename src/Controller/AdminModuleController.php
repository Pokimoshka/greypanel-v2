<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\JsonResponse;
use GreyPanel\Service\ModuleService;

class AdminModuleController
{
    public function __construct(
        private ModuleService $moduleService
    ) {}

    public function index(Request $request): Response
    {
        $modules = $this->moduleService->getAll();
        return new Response(View::render('modules.tpl', [
            'modules' => $modules,
        ]));
    }

    public function toggle(Request $request): JsonResponse
    {
        $moduleName = $request->post('module');
        $enabled = (bool)$request->post('enabled');

        if (!$moduleName) {
            return new JsonResponse(['success' => false, 'error' => 'Module name required']);
        }

        $this->moduleService->setEnabled($moduleName, $enabled);
        return new JsonResponse(['success' => true]);
    }
}