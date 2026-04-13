<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface MarkdownServiceInterface
{
    public function parse(string $markdown): string;
}