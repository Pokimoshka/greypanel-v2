<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Service\SeoService;
use GreyPanel\Service\SettingsService;

class AdminSeoController
{
    public function __construct(
        private SeoService $seoService,
        private SettingsService $settings
    ) {}

    public function index(Request $request): Response
    {
        if ($request->isPost()) {
            $this->settings->set('seo_default_description', $request->post('seo_default_description', ''));
            $this->settings->set('seo_keywords', $request->post('seo_keywords', ''));
            $this->seoService->setSitemapEnabled($request->post('seo_sitemap_enabled') === '1');
            $this->seoService->saveRobotsTxt($request->post('robots_txt', ''));

            if ($this->seoService->isSitemapEnabled()) {
                $this->seoService->saveSitemap();
            }
        }

        return new Response(View::render('seo.tpl', [
            'robots_txt' => $this->seoService->getRobotsTxt(),
            'seo_default_description' => $this->settings->get('seo_default_description', ''),
            'seo_keywords' => $this->settings->get('seo_keywords', ''),
            'seo_sitemap_enabled' => $this->seoService->isSitemapEnabled(),
        ]));
    }

    public function regenerateSitemap(): Response
    {
        $this->seoService->saveSitemap();
        return new Response('OK');
    }
}