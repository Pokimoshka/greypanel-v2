<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Service\LocaleManager;
use GreyPanel\Service\SiteService;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminSiteController
{
    public function __construct(
        private SiteService $siteService,
        private SessionServiceInterface $session,
        private SettingsServiceInterface $settings,
        private TranslatorInterface $translator,
        private LocaleManager $localeManager
    ) {
    }

    public function index(Request $request): Response
    {
        $protocol = $this->siteService->getProtocol();
        $manualUrl = $this->siteService->getManualUrl();
        $currentUrl = $this->siteService->getCurrentDisplayUrl();
        $siteName = $this->settings->get('site_name', 'GreyPanel');
        $appDebug = $this->settings->getBool('app_debug', false);

        $sessionLifetime = $this->settings->getInt('session_lifetime', 7200);
        $sessionName = $this->settings->get('session_name', 'greysession');
        $defaultLanguage = $this->settings->get('default_language', 'ru');

        return new Response(View::render('site_settings.tpl', [
            'protocol' => $protocol,
            'manual_url' => $manualUrl,
            'current_url' => $currentUrl,
            'site_name' => $siteName,
            'app_debug' => $appDebug,
            'session_lifetime' => $sessionLifetime,
            'session_name' => $sessionName,
            'default_language' => $defaultLanguage,
        ]));
    }

    public function save(Request $request): Response
    {
        $protocol = $request->postString('site_protocol');
        $manualUrl = trim($request->postString('site_url_manual', ''));
        $this->siteService->saveSettings($protocol, $manualUrl);

        $siteName = trim($request->postString('site_name'));
        if (!empty($siteName)) {
            $this->settings->set('site_name', $siteName);
        }

        $appDebug = $request->postBool('app_debug') ? '1' : '0';
        $this->settings->set('app_debug', $appDebug);

        $sessionLifetime = $request->postInt('session_lifetime');
        if ($sessionLifetime > 0) {
            $this->settings->set('session_lifetime', $sessionLifetime);
        }

        $sessionName = trim($request->postString('session_name'));
        if (!empty($sessionName) && preg_match('/^[a-zA-Z0-9_]+$/', $sessionName)) {
            $this->settings->set('session_name', $sessionName);
        }

        $defaultLanguage = trim($request->postString('default_language'));
        $availableLocales = $this->localeManager->getAvailableLocales();
        if (in_array($defaultLanguage, $availableLocales, true)) {
            $this->settings->set('default_language', $defaultLanguage);
        }

        $this->session->setFlash('success', $this->translator->trans('admin.site_settings_saved'));
        return new RedirectResponse('/admin/site-settings');
    }
}
