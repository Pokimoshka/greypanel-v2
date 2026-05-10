<?php

declare(strict_types=1);

namespace GreyPanel\Helper;

final class FlagsHelper
{
    public static function normalize(string $flags): string
    {
        $flags = mb_strtolower(trim($flags));
        $chars = [];
        foreach (str_split($flags) as $char) {
            if (ctype_lower($char)) {
                $chars[] = $char;
            }
        }
        $chars = array_unique($chars);
        sort($chars, SORT_STRING);
        return implode('', $chars);
    }
}
