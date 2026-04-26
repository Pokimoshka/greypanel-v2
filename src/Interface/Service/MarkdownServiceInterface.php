<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

interface MarkdownServiceInterface
{
    public function parse(string $markdown): string;
}
