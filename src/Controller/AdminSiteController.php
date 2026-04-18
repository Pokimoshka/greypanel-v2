<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Service\SiteService;

class AdminSiteController
{
    public function __construct(private SiteService $siteService) {}

    public function index(Request $request): Response
    {
        $protocol = $this->siteService->getProtocol();
        $manualUrl = $this->siteService->getManualUrl(); // исправлено
        $currentUrl = $this->siteService->getCurrentDisplayUrl();

        return new Response(View::render('site_settings.tpl', [
            'protocol' => $protocol,
            'manual_url' => $manualUrl,
            'current_url' => $currentUrl,
        ]));
    }

    public function save(Request $request): Response
    {
        $protocol = $request->post('site_protocol');
        $manualUrl = trim($request->post('site_url_manual', ''));
        $this->siteService->saveSettings($protocol, $manualUrl);
        return new RedirectResponse('/admin/site-settings');
    }
}