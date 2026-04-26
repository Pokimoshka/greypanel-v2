<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Interface\Service\SettingsServiceInterface;

class SiteService
{
    private SettingsServiceInterface $settings;

    public function __construct(SettingsServiceInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Возвращает полный URL сайта (с протоколом) без завершающего слеша.
     */
    public function getSiteUrl(): string
    {
        $manualUrl = $this->settings->get('site_url_manual', '');
        if (!empty($manualUrl)) {
            return rtrim($manualUrl, '/');
        }

        $protocol = $this->getProtocol();
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    /**
     * Определяет протокол на основе настроек и текущего запроса.
     */
    public function getProtocol(): string
    {
        $setting = $this->settings->get('site_protocol', 'auto');
        if ($setting === 'http') {
            return 'http';
        }
        if ($setting === 'https') {
            return 'https';
        }
        // auto – определяем по запросу
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return 'https';
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return 'https';
        }
        return 'http';
    }

    /**
     * Сохраняет настройки протокола и ручного URL.
     */
    public function saveSettings(string $protocol, string $manualUrl): void
    {
        if (!in_array($protocol, ['auto', 'http', 'https'])) {
            $protocol = 'auto';
        }
        $this->settings->set('site_protocol', $protocol);
        $this->settings->set('site_url_manual', $manualUrl);
    }

    /**
     * Возвращает текущий URL для отображения в админке.
     */
    public function getCurrentDisplayUrl(): string
    {
        $manual = $this->settings->get('site_url_manual', '');
        if (!empty($manual)) {
            return $manual;
        }
        $protocol = $this->getProtocol();
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    /**
     * Возвращает сырое значение ручного URL из настроек (без обработки).
     */
    public function getManualUrl(): string
    {
        return $this->settings->get('site_url_manual', '');
    }
}
