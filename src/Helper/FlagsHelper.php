<?php

declare(strict_types=1);

namespace GreyPanel\Helper;

final class FlagsHelper
{
    public static function normalize(string $flags): string
    {
        if ($flags === '') {
            return '';
        }
        $chars = array_unique(str_split($flags));
        sort($chars, SORT_STRING);
        return implode('', $chars);
    }
}
