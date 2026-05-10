<?php

declare(strict_types=1);

namespace GreyPanel\Merchant;

abstract class BaseMerchant implements MerchantInterface
{
    protected function getForm(string $url, array $params): string
    {
        $html = '<form id="pay_form" method="post" action="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
        foreach ($params as $name => $value) {
            $html .= '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" />';
        }
        $html .= '</form>';
        $html .= '<script>document.getElementById("pay_form").submit();</script>';

        return $html;
    }

    protected function getLink(string $url, array $params = []): string
    {
        $query = http_build_query($params);
        $fullUrl = $query ? $url . '?' . $query : $url;

        return '<script>document.location.href = ' . json_encode($fullUrl) . ';</script>';
    }
}
