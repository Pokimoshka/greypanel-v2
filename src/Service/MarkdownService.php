<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

final class MarkdownService implements MarkdownServiceInterface
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'escape', 
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    public function parse(string $markdown): string
    {
        return $this->converter->convert($markdown)->getContent();
    }
}