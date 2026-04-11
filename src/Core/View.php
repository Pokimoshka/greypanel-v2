<?php
declare(strict_types=1);

namespace GreyPanel\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

final class View
{
    private static ?Environment $twig = null;

    public static function init(string $templatePath, string $cachePath, bool $debug = false): void
    {
        $loader = new FilesystemLoader($templatePath);
        self::$twig = new Environment($loader, [
            'cache' => $debug ? false : $cachePath,
            'debug' => $debug,
            'autoescape' => 'html',
        ]);

        if ($debug) {
            self::$twig->addExtension(new \Twig\Extension\DebugExtension());
        }

        self::registerExtensions();
    }

    private static function registerExtensions(): void
    {
        self::$twig->addFunction(new TwigFunction('vite_assets', [self::class, 'viteAssets'], ['is_safe' => ['html']]));
    }

    public static function viteAssets(string ...$entries): string
    {
        $map = [
            'vendor' => 'resources/js/app',
            'vendor_style' => 'resources/scss/style',
        ];

        $manifestPath = __DIR__ . '/../../public/assets/manifest.json';
        if (!file_exists($manifestPath)) {
            return '<!-- manifest.json not found -->';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $html = '';

        foreach ($entries as $entry) {
            $realEntry = $map[$entry] ?? $entry;
            $keyJs = $realEntry . '.js';
            $keyCss = $realEntry . '.scss';

            if (isset($manifest[$keyJs])) {
                $file = $manifest[$keyJs]['file'];
                $html .= sprintf('<script type="module" src="/assets/%s"></script>', $file);
                foreach ($manifest[$keyJs]['css'] ?? [] as $cssFile) {
                    $html .= sprintf('<link rel="stylesheet" href="/assets/%s">', $cssFile);
                }
            } elseif (isset($manifest[$keyCss])) {
                $file = $manifest[$keyCss]['file'];
                $html .= sprintf('<link rel="stylesheet" href="/assets/%s">', $file);
            }
        }
        return $html;
    }

    public static function render(string $template, array $data = []): string
    {
        if (!self::$twig) {
            throw new \RuntimeException('Twig not initialized');
        }
        return self::$twig->render($template, $data);
    }

    public static function getTwig(): Environment
    {
        if (!self::$twig) {
            throw new \RuntimeException('Twig not initialized');
        }
        return self::$twig;
    }

    public static function addGlobal(string $name, $value): void
    {
        self::getTwig()->addGlobal($name, $value);
    }
}