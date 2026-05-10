<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Service\ModuleService;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminModuleController extends AbstractController
{
    public function __construct(
        private ModuleService $moduleService,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        parent::__construct($serializer, $validator, $translator);
    }

    public function index(Request $request): Response
    {
        $modules = $this->moduleService->getAll();
        return new Response(View::render('modules.tpl', [
            'modules' => $modules,
        ]));
    }

    public function toggle(Request $request): JsonResponse
    {
        $moduleName = $request->postString('module');
        $enabled = $request->postBool('enabled');

        if (!$moduleName) {
            return $this->json(['success' => false, 'error' => 'Module name required']);
        }

        $this->moduleService->setEnabled($moduleName, $enabled);
        return $this->json(['success' => true]);
    }
}
