<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Service\LocaleManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class LanguageController
{
    public function __construct(
        private SessionServiceInterface $session,
        private TranslatorInterface $translator,
        private UserRepositoryInterface $userRepo,
        private LocaleManager $localeManager
    ) {
    }

    public function switch(Request $request, string $lang): RedirectResponse
    {
        $availableLocales = $this->localeManager->getAvailableLocales();

        if (in_array($lang, $availableLocales, true)) {
            $this->session->setLocale($lang);

            $userId = $this->session->getUser()?->getId();
            if ($userId) {
                $user = $this->userRepo->findById($userId);
                if ($user) {
                    $user->setLang($lang);
                    $this->userRepo->update($user);
                }
            }
        }

        $referer = $_SERVER['HTTP_REFERER'];
        return new RedirectResponse($referer);
    }
}
