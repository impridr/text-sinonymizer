<?php

namespace Profile\Text\Sinonymizer;

use \Profile\Http\Client\CurlClient;
use \Profile\Text\Translator\DictChromeExGoogleTranslator;

class TranslatorSinonymizer extends DictChromeExGoogleTranslator implements SinonymizerInterface
{
    protected array $languages = [];

    public static function create(array $languages = []): self
    {
        return (new static(new CurlClient()))->withLanguages($languages);
    }
    
    public function setLanguages(array $languages): void
    {
        $this->languages = [];

        $sourceLanguage = array_shift($languages);
        $languages[] = $sourceLanguage;

        while ($targetLanguage = array_shift($languages))
            $sourceLanguage !== $targetLanguage
                ? $this->languages[] = [$sourceLanguage, $sourceLanguage = $targetLanguage]
                : $sourceLanguage = $targetLanguage;
    }

    public function withLanguages(array $languages): self
    {
        $clone = clone($this);
        $clone->setLanguages($languages);

        return $clone;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function sinonymize(string $text): string
    {
        return array_reduce(
            $this->getLanguages(),
            fn ($text, $languagePair) => $this->translate($text, ... $languagePair),
            $text
        );
    }
}