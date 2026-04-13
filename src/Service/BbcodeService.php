<?php
declare(strict_types=1);

namespace GreyPanel\Service;

final class BbcodeService implements BbcodeServiceInterface
{
    private array $allowedSchemes = ['http', 'https'];

    public function parse(string $text): string
    {
        // Экранируем весь HTML
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Применяем BB-теги
        $text = $this->parseBold($text);
        $text = $this->parseItalic($text);
        $text = $this->parseUnderline($text);
        $text = $this->parseUrl($text);
        $text = $this->parseImage($text);
        $text = $this->parseQuote($text);
        $text = $this->parseCode($text);

        // Преобразуем переносы строк в <br>
        return nl2br($text);
    }

    private function parseBold(string $text): string
    {
        return preg_replace_callback(
            '#\[b\](.*?)\[/b\]#is',
            fn($m) => '<b>' . $this->parse($m[1]) . '</b>',
            $text
        );
    }

    private function parseItalic(string $text): string
    {
        return preg_replace_callback(
            '#\[i\](.*?)\[/i\]#is',
            fn($m) => '<i>' . $this->parse($m[1]) . '</i>',
            $text
        );
    }

    private function parseUnderline(string $text): string
    {
        return preg_replace_callback(
            '#\[u\](.*?)\[/u\]#is',
            fn($m) => '<u>' . $this->parse($m[1]) . '</u>',
            $text
        );
    }

    private function parseUrl(string $text): string
    {
        // [url]http://example.com[/url]
        $text = preg_replace_callback(
            '#\[url\](.*?)\[/url\]#is',
            function ($m) {
                $url = $this->sanitizeUrl($m[1]);
                return $url ? '<a href="' . $url . '" target="_blank" rel="nofollow noopener">' . $this->parse($m[1]) . '</a>' : $m[0];
            },
            $text
        );

        // [url=http://example.com]текст[/url]
        return preg_replace_callback(
            '#\[url=(.*?)\](.*?)\[/url\]#is',
            function ($m) {
                $url = $this->sanitizeUrl($m[1]);
                return $url ? '<a href="' . $url . '" target="_blank" rel="nofollow noopener">' . $this->parse($m[2]) . '</a>' : $m[0];
            },
            $text
        );
    }

    private function parseImage(string $text): string
    {
        return preg_replace_callback(
            '#\[img\](.*?)\[/img\]#is',
            function ($m) {
                $url = $this->sanitizeUrl($m[1]);
                if (!$url) {
                    return $m[0];
                }
                // Дополнительно можно проверять Content-Type, но это замедлит вывод
                return '<img src="' . $url . '" alt="User image" class="img-fluid" style="max-width:100%">';
            },
            $text
        );
    }

    private function parseQuote(string $text): string
    {
        // [quote=Author]текст[/quote]
        $text = preg_replace_callback(
            '#\[quote=(.*?)\](.*?)\[/quote\]#is',
            fn($m) => '<blockquote class="blockquote"><small>' . htmlspecialchars($m[1]) . ' wrote:</small><br>' . $this->parse($m[2]) . '</blockquote>',
            $text
        );

        // [quote]текст[/quote]
        return preg_replace_callback(
            '#\[quote\](.*?)\[/quote\]#is',
            fn($m) => '<blockquote class="blockquote">' . $this->parse($m[1]) . '</blockquote>',
            $text
        );
    }

    private function parseCode(string $text): string
    {
        return preg_replace_callback(
            '#\[code\](.*?)\[/code\]#is',
            fn($m) => '<pre><code>' . htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8') . '</code></pre>',
            $text
        );
    }

    private function sanitizeUrl(string $url): ?string
    {
        $url = trim($url);
        $parts = parse_url($url);
        if (!isset($parts['scheme'])) {
            $url = 'http://' . $url;
            $parts = parse_url($url);
        }
        if (!$parts || !in_array(strtolower($parts['scheme']), $this->allowedSchemes, true)) {
            return null;
        }
        // Дополнительно можно запретить IP-адреса, localhost и т.д.
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}