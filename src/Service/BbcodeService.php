<?php
declare(strict_types=1);

namespace GreyPanel\Service;

final class BbcodeService implements BbcodeServiceInterface
{
    public function parse(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        $search = [
            '#\[b\](.*?)\[/b\]#is',
            '#\[i\](.*?)\[/i\]#is',
            '#\[u\](.*?)\[/u\]#is',
            '#\[url\](.*?)\[/url\]#is',
            '#\[url=(.*?)\](.*?)\[/url\]#is',
            '#\[img\](.*?)\[/img\]#is',
            '#\[quote\](.*?)\[/quote\]#is',
            '#\[quote=(.*?)\](.*?)\[/quote\]#is',
            '#\[code\](.*?)\[/code\]#is',
        ];
        $replace = [
            '<b>$1</b>',
            '<i>$1</i>',
            '<u>$1</u>',
            '<a href="$1" target="_blank" rel="nofollow">$1</a>',
            '<a href="$1" target="_blank" rel="nofollow">$2</a>',
            '<img src="$1" class="img-fluid" style="max-width: 100%;">',
            '<blockquote class="blockquote">$1</blockquote>',
            '<blockquote class="blockquote"><small>$1 wrote:</small><br>$2</blockquote>',
            '<pre><code>$1</code></pre>',
        ];
        $text = preg_replace($search, $replace, $text);
        return nl2br($text);
    }
}