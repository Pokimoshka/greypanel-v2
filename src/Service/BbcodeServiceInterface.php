<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface BbcodeServiceInterface
{
    public function parse(string $text): string;
}