<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Exception\AuthenticationException;
use GreyPanel\Interface\Repository\LogRepositoryInterface;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\AuthServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Service\RecaptchaService;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AuthController
{
    public function __construct(
        private AuthServiceInterface $auth,
        private LogRepositoryInterface $logRepo,
        private SessionServiceInterface $session,
        private UserRepositoryInterface $userRepo,
        private RecaptchaService $recaptcha,
        private SettingsServiceInterface $settings,
        private TranslatorInterface $translator
    ) {
    }

    public function login(Request $request): Response
    {
        $recaptchaEnabled = $this->settings->getBool('recaptcha_enabled', false);
        $recaptchaSiteKey = $this->settings->get('recaptcha_site_key', '');

        if ($request->isPost()) {
            if ($recaptchaEnabled) {
                $recaptchaResponse = $request->post('g-recaptcha-response');
                if (!$this->recaptcha->verify($recaptchaResponse, $request->getClientIp())) {
                    return new Response(View::render('auth/login.tpl', [
                        'error' => $this->translator->trans('auth.captcha_required'),
                        'recaptcha_enabled' => $recaptchaEnabled,
                        'recaptcha_site_key' => $recaptchaSiteKey,
                    ]));
                }
            }

            $login = $request->postString('username');
            $password = $request->postString('password');
            $remember = $request->postBool('remember');

            try {
                $user = $this->auth->login($login, $password);
            } catch (AuthenticationException $e) {
                return new Response(View::render('auth/login.tpl', [
                    'error' => $e->getMessage(),
                    'recaptcha_enabled' => $recaptchaEnabled,
                    'recaptcha_site_key' => $recaptchaSiteKey,
                ]));
            }

            $this->session->setUser($user);
            $this->logRepo->add($user->getId(), 'login', $this->translator->trans('auth.logged_in'));

            if ($remember) {
                $token = $this->auth->setRememberToken($user);
                setcookie('remember_token', $token, time() + 86400 * 30, '/', '', false, true);
            }

            return new RedirectResponse('/');
        }

        return new Response(View::render('auth/login.tpl', [
            'recaptcha_enabled' => $recaptchaEnabled,
            'recaptcha_site_key' => $recaptchaSiteKey,
        ]));
    }

    public function register(Request $request): Response
    {
        $recaptchaEnabled = $this->settings->getBool('recaptcha_enabled', false);
        $recaptchaSiteKey = $this->settings->get('recaptcha_site_key', '');

        if ($request->get('ref') && !$this->session->isLoggedIn()) {
            $refId = (int)$request->get('ref');
            if ($this->userRepo->findById($refId)) {
                $this->session->setReferralId($refId);
            }
        }

        if ($request->isPost()) {
            if ($recaptchaEnabled) {
                $recaptchaResponse = $request->post('g-recaptcha-response');
                if (!$this->recaptcha->verify($recaptchaResponse, $request->getClientIp())) {
                    return new Response(View::render('auth/register.tpl', [
                        'error' => $this->translator->trans('auth.captcha_required'),
                        'recaptcha_enabled' => $recaptchaEnabled,
                        'recaptcha_site_key' => $recaptchaSiteKey,
                    ]));
                }
            }

            $username = $request->postString('username');
            $email = $request->postString('email');
            $password = $request->postString('password');
            $password2 = $request->postString('password2');
            $ip = (string)$request->getClientIp();
            $referralId = $this->session->getReferralId() ?? 0;

            try {
                $user = $this->auth->register($username, $email, $password, $password2, $ip, $referralId);
            } catch (AuthenticationException $e) {
                return new Response(View::render('auth/register.tpl', [
                    'error' => $e->getMessage(),
                    'username' => $username,
                    'email' => $email,
                    'recaptcha_enabled' => $recaptchaEnabled,
                    'recaptcha_site_key' => $recaptchaSiteKey,
                ]));
            }

            $this->session->setUser($user);
            $this->session->clearReferralId();
            $this->logRepo->add($user->getId(), 'register', $this->translator->trans('auth.registered'));
            return new RedirectResponse('/');
        }

        return new Response(View::render('auth/register.tpl', [
            'recaptcha_enabled' => $recaptchaEnabled,
            'recaptcha_site_key' => $recaptchaSiteKey,
        ]));
    }

    public function logout(Request $request): Response
    {
        $userId = $this->session->getUser()?->getId();
        if ($userId) {
            $user = $this->auth->getUserById($userId);
            if ($user) {
                $this->auth->clearRememberToken($user);
            }
            setcookie('remember_token', '', time() - 3600, '/');
        }
        $this->session->clear();
        return new RedirectResponse('/');
    }
}
