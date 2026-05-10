<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use Symfony\Component\Yaml\Yaml;

class LocaleManager
{
    /** @var string[] */
    private array $locales;

    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    public function getAvailableLocales(): array
    {
        return $this->locales;
    }

    public function getLanguageNames(): array
    {
        $names = [];
        $transDir = ROOT_DIR . '/resources/translations/';
        foreach ($this->locales as $locale) {
            $file = $transDir . 'messages.' . $locale . '.yaml';
            if (file_exists($file)) {
                $data = Yaml::parseFile($file);
                $names[$locale] = $data['language']['name'] ?? $locale;
            } else {
                $names[$locale] = $locale;
            }
        }
        return $names;
    }
}
