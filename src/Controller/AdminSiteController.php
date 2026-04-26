<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Service\SiteService;

class AdminSiteController
{
    public function __construct(
        private SiteService $siteService,
        private SettingsServiceInterface $settings
    ) {
    }

    public function index(Request $request): Response
    {
        // Основные настройки
        $protocol = $this->siteService->getProtocol();
        $manualUrl = $this->siteService->getManualUrl();
        $currentUrl = $this->siteService->getCurrentDisplayUrl();
        $siteName = $this->settings->get('site_name', 'GreyPanel');
        $appDebug = $this->settings->getBool('app_debug', false);

        // Расширенные настройки (сессии)
        $sessionLifetime = $this->settings->getInt('session_lifetime', 7200);
        $sessionName = $this->settings->get('session_name', 'greysession');

        return new Response(View::render('site_settings.tpl', [
            'protocol' => $protocol,
            'manual_url' => $manualUrl,
            'current_url' => $currentUrl,
            'site_name' => $siteName,
            'app_debug' => $appDebug,
            'session_lifetime' => $sessionLifetime,
            'session_name' => $sessionName,
        ]));
    }

    public function save(Request $request): Response
    {
        // Сохраняем настройки протокола и ручного URL
        $protocol = $request->post('site_protocol');
        $manualUrl = trim($request->post('site_url_manual', ''));
        $this->siteService->saveSettings($protocol, $manualUrl);

        // Сохраняем название сайта и режим отладки
        $siteName = trim($request->post('site_name'));
        if (!empty($siteName)) {
            $this->settings->set('site_name', $siteName);
        }

        $appDebug = $request->post('app_debug') ? '1' : '0';
        $this->settings->set('app_debug', $appDebug);

        // Сохраняем настройки сессии
        $sessionLifetime = (int)$request->post('session_lifetime');
        if ($sessionLifetime > 0) {
            $this->settings->set('session_lifetime', (string)$sessionLifetime);
        }

        $sessionName = trim($request->post('session_name'));
        if (!empty($sessionName) && preg_match('/^[a-zA-Z0-9_]+$/', $sessionName)) {
            $this->settings->set('session_name', $sessionName);
        }

        $_SESSION['flash_success'] = 'Настройки сохранены. Для применения изменений сессии выйдете и зайдите заново.';
        return new RedirectResponse('/admin/site-settings');
    }
}
