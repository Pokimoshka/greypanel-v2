<?php

declare(strict_types=1);

namespace GreyPanel\Middleware;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Repository\UserRepository;
use GreyPanel\Service\LocaleManager;
use Symfony\Component\Translation\Translator;

class LocaleMiddleware
{
    public function __construct(
        private Translator                 $translator,
        private SessionServiceInterface    $session,
        private UserRepository             $userRepo,
        private SettingsServiceInterface   $settings,
        private LocaleManager              $localeManager
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $available = $this->localeManager->getAvailableLocales();
        $locale = null;

        $locale = $this->session->getLocale();

        if (!$locale && $this->session->isLoggedIn()) {
            $user = $this->userRepo->findById($this->session->getUser()?->getId());
            $locale = $user?->getLang();
        }

        if (!$locale) {
            $locale = $this->detectLocaleFromHeader($request);
        }

        if (!$locale || !in_array($locale, $available, true)) {
            $locale = $this->settings->get('default_language');
        }

        if (!$locale || !in_array($locale, $available, true)) {
            $locale = 'ru';
        }

        $this->translator->setLocale($locale);

        if (!$this->session->getLocale()) {
            $this->session->setLocale($locale);
        }

        View::addGlobal('locale', $locale);

        return $next($request);
    }

    private function detectLocaleFromHeader(Request $request): ?string
    {
        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (empty($header)) {
            return null;
        }
        $parts = explode(',', $header);
        $availableLocales = $this->localeManager->getAvailableLocales();
        foreach ($parts as $part) {
            $locale = trim(explode(';', $part)[0]);
            if (in_array($locale, $availableLocales, true)) {
                return $locale;
            }
        }
        return null;
    }
}
