<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Response;
use GreyPanel\Service\SeoService;

class SitemapController
{
    public function __construct(private SeoService $seoService)
    {
    }

    public function index(): Response
    {
        if (!$this->seoService->isSitemapEnabled()) {
            return new Response('Sitemap disabled', 404);
        }

        $xml = $this->seoService->generateSitemap();
        return new Response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
