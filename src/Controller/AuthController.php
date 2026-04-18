<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Service\AuthServiceInterface;
use GreyPanel\Repository\LogRepositoryInterface;
use GreyPanel\Service\SessionServiceInterface;
use GreyPanel\Repository\UserRepositoryInterface;
use GreyPanel\Service\RecaptchaService;
use GreyPanel\Service\SettingsServiceInterface;

final class AuthController
{
    public function __construct(
        private AuthServiceInterface $auth,
        private LogRepositoryInterface $logRepo,
        private SessionServiceInterface $session,
        private UserRepositoryInterface $userRepo,
        private RecaptchaService $recaptcha,
        private SettingsServiceInterface $settings
    ) {}

    public function login(Request $request): Response
    {
        if ($request->isPost()) {
            // Проверка reCAPTCHA (только при POST)
            $recaptchaEnabled = $this->settings->getBool('recaptcha_enabled', false);
            if ($recaptchaEnabled) {
                $recaptchaResponse = $request->post('g-recaptcha-response');
                if (!$this->recaptcha->verify($recaptchaResponse, $_SERVER['REMOTE_ADDR'])) {
                    return new Response(View::render('auth/login.tpl', [
                        'error' => 'Пожалуйста, подтвердите, что вы не робот.',
                        'recaptcha_site_key' => $this->settings->get('recaptcha_site_key', '')
                    ]));
                }
            }

            $login = $request->post('username');
            $password = $request->post('password');
            $remember = (bool)$request->post('remember');

            $result = $this->auth->login($login, $password);
            if ($result instanceof \GreyPanel\Model\User) {
                $this->session->setUser($result);
                $_SESSION['user']['updated_at'] = $result->getUpdatedAt();
                $this->logRepo->add($result->getId(), 'login', 'Пользователь вошёл');

                if ($remember) {
                    $token = $this->auth->setRememberToken($result);
                    setcookie('remember_token', $token, time() + 86400 * 30, '/', '', false, true);
                }

                return new RedirectResponse('/');
            }

            return new Response(View::render('auth/login.tpl', [
                'error' => $result,
                'recaptcha_site_key' => $this->settings->get('recaptcha_site_key', '')
            ]));
        }

        // GET запрос – показываем форму
        return new Response(View::render('auth/login.tpl', [
            'recaptcha_site_key' => $this->settings->get('recaptcha_site_key', '')
        ]));
    }

    public function register(Request $request): Response
    {
        if ($request->get('ref') && !$this->session->isLoggedIn()) {
            $refId = (int)$request->get('ref');
            if ($this->userRepo->findById($refId)) {
                $_SESSION['referral'] = $refId;
            }
        }

        if ($request->isPost()) {
            // Проверка reCAPTCHA
            $recaptchaEnabled = $this->settings->getBool('recaptcha_enabled', false);
            if ($recaptchaEnabled) {
                $recaptchaResponse = $request->post('g-recaptcha-response');
                if (!$this->recaptcha->verify($recaptchaResponse, $_SERVER['REMOTE_ADDR'])) {
                    return new Response(View::render('auth/register.tpl', [
                        'error' => 'Пожалуйста, подтвердите, что вы не робот.',
                        'recaptcha_site_key' => $this->settings->get('recaptcha_site_key', '')
                    ]));
                }
            }

            $username = $request->post('username');
            $email = $request->post('email');
            $password = $request->post('password');
            $password2 = $request->post('password2');
            $ip = $_SERVER['REMOTE_ADDR'];
            $referralId = $_SESSION['referral'] ?? 0;

            $result = $this->auth->register($username, $email, $password, $password2, $ip, $referralId);
            if ($result instanceof \GreyPanel\Model\User) {
                $this->session->setUser($result);
                $this->logRepo->add($result->getId(), 'register', 'Пользователь зарегистрировался');
                unset($_SESSION['referral']);
                return new RedirectResponse('/');
            }

            return new Response(View::render('auth/register.tpl', [
                'error' => $result,
                'username' => $username,
                'email' => $email,
                'recaptcha_site_key' => $this->settings->get('recaptcha_site_key', '')
            ]));
        }

        return new Response(View::render('auth/register.tpl', [
            'recaptcha_site_key' => $this->settings->get('recaptcha_site_key', '')
        ]));
    }

    public function logout(Request $request): Response
    {
        $userId = $this->session->getUserId();
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