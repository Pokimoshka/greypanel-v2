<?php

namespace GreyPanel\Service;

class RecaptchaService
{
    private SettingsServiceInterface $settings;
    private EncryptionServiceInterface $encryption;

    public function __construct(SettingsServiceInterface $settings, EncryptionServiceInterface $encryption)
    {
        $this->settings = $settings;
        $this->encryption = $encryption;
    }

    public function verify(string $response, ?string $clientIp = null): bool
    {
        $enabled = $this->settings->getBool('recaptcha_enabled', false);
        if (!$enabled) {
            return true; // Капча выключена – всегда успех
        }

        $encryptedSecret = $this->settings->get('recaptcha_secret_key', '');
        if (empty($encryptedSecret)) {
            error_log('reCAPTCHA: secret key is empty');
            return false;
        }

        try {
            $secretKey = $this->encryption->decrypt($encryptedSecret);
            if ($secretKey === false || empty($secretKey)) {
                error_log('reCAPTCHA: failed to decrypt secret key');
                return false;
            }
        } catch (\Throwable $e) {
            error_log('reCAPTCHA decryption error: ' . $e->getMessage());
            return false;
        }

        if (empty($response)) {
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $response,
        ];
        if ($clientIp) {
            $data['remoteip'] = $clientIp;
        }

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === false) {
            error_log('reCAPTCHA: failed to call siteverify API');
            return false;
        }
        $json = json_decode($result, true);
        return isset($json['success']) && $json['success'] === true;
    }
}