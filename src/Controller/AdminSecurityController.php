<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;

class AdminSecurityController
{
    public function __construct(
        private SettingsServiceInterface $settings,
        private EncryptionServiceInterface $encryption
    ) {
    }

    public function index(Request $request): Response
    {
        $recaptchaEnabled = $this->settings->getBool('recaptcha_enabled', false);
        $recaptchaSiteKey = $this->settings->get('recaptcha_site_key', '');
        $recaptchaSecretKeyEncrypted = $this->settings->get('recaptcha_secret_key', '');
        $recaptchaSecretKey = $recaptchaSecretKeyEncrypted ? $this->encryption->decrypt($recaptchaSecretKeyEncrypted) : '';

        return new Response(View::render('security.tpl', [
            'recaptcha_enabled' => $recaptchaEnabled,
            'recaptcha_site_key' => $recaptchaSiteKey,
            'recaptcha_secret_key' => $recaptchaSecretKey,
        ]));
    }

    public function save(Request $request): Response
    {
        $recaptchaEnabled = (bool)$request->post('recaptcha_enabled');
        $recaptchaSiteKey = trim($request->post('recaptcha_site_key'));
        $recaptchaSecretKey = trim($request->post('recaptcha_secret_key'));

        $this->settings->set('recaptcha_enabled', $recaptchaEnabled ? '1' : '0');
        $this->settings->set('recaptcha_site_key', $recaptchaSiteKey);
        if (!empty($recaptchaSecretKey)) {
            $encrypted = $this->encryption->encrypt($recaptchaSecretKey);
            $this->settings->set('recaptcha_secret_key', $encrypted);
        }

        return new RedirectResponse('/admin/security');
    }
}
