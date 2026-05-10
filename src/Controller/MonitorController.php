<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\SeoServiceInterface;
use GreyPanel\Service\MonitorService;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MonitorController extends AbstractController
{
    private MonitorService $monitorService;
    private SeoServiceInterface $seoService;

    public function __construct(
        MonitorService $monitorService,
        SeoServiceInterface $seoService,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        parent::__construct($serializer, $validator, $translator);
        $this->monitorService = $monitorService;
        $this->seoService = $seoService;
    }

    public function index(Request $request): Response
    {
        $meta = $this->seoService->getMetaTags('Мониторинг', 'Мониторинг игровых серверов');
        return new Response(View::render('monitor/index.tpl', [
            'meta_title' => $meta['title'],
            'meta_description' => $meta['description'],
            'meta_keywords' => $meta['keywords'],
        ]));
    }

    public function data(Request $request): JsonResponse
    {
        $servers = $this->monitorService->getServers();
        return $this->json($servers);
    }
}
