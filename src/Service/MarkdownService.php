<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Interface\Service\MarkdownServiceInterface;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;

final class MarkdownService implements MarkdownServiceInterface
{
    private MarkdownConverter $converter;

    public function __construct(
        private HtmlSanitizer $sanitizer
    ) {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());

        $environment->addRenderer(BlockQuote::class, new class () implements NodeRendererInterface {
            public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
            {
                $innerHtml = $childRenderer->renderNodes($node->children());

                if (preg_match('/^<strong>(.+?)<\/strong>\s*\(#(\d+)\)/u', $innerHtml, $matches)) {
                    $author = $matches[1];
                    $postId = $matches[2];
                    $rest = trim(substr($innerHtml, strlen($matches[0])));
                    $rest = preg_replace('#^(<br\s*/?>|\n)+#i', '', $rest);

                    $header = new HtmlElement(
                        'div',
                        ['class' => 'quote-header'],
                        'Цитата: ' . new HtmlElement('a', [
                            'href' => "/forum/thread/{$postId}#post-{$postId}"
                        ], $author)
                    );
                    $body = new HtmlElement('div', ['class' => 'quote-body'], $rest);

                    return (string) new HtmlElement('blockquote', ['class' => 'xen-quote'], $header . $body);
                }

                return (string) new HtmlElement('blockquote', ['class' => 'xen-quote'], $innerHtml);
            }

            public function getXmlTagName(): string
            {
                return 'block_quote';
            }
            public function getXmlNamespace(): string
            {
                return 'http://commonmark.org/xml/1.0';
            }
        });

        $this->converter = new MarkdownConverter($environment);
    }

    public function parse(string $markdown): string
    {
        $html = $this->converter->convert($markdown)->getContent();

        $safeHtml = $this->sanitizer->sanitize($html);

        return $safeHtml;
    }
}
