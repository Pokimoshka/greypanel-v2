<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Service\SeoService;
use GreyPanel\Service\SettingsService;

class AdminSeoController
{
    public function __construct(
        private SeoService $seoService,
        private SettingsService $settings,
        private SessionServiceInterface $session,
    ) {
    }

    public function index(Request $request): Response
    {
        if ($request->isPost()) {
            $this->settings->set('seo_default_description', $request->postString('seo_default_description', ''));
            $this->settings->set('seo_keywords', $request->postString('seo_keywords', ''));
            $this->seoService->setSitemapEnabled($request->postString('seo_sitemap_enabled') === '1');
            $this->seoService->saveRobotsTxt($request->postString('robots_txt', ''));

            if ($this->seoService->isSitemapEnabled()) {
                $this->seoService->saveSitemap();
            }
        }

        $this->session->setFlash('success', 'SEO настройки сохранены');

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

    public function robots(): Response
    {
        $content = $this->seoService->getRobotsTxt();
        return new Response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
