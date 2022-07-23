<?php

namespace Profile\Text\Sinonymizer;

use \Profile\Http\Client\CurlClient;
use \Profile\Text\Translator\{TranslatorInterface, DictChromeExGoogleTranslator};

class TranslatorSinonymizer implements SinonymizerInterface
{
    protected TranslatorInterface $translator;
    protected array $languages = [];

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function create(array $languages = []): self
    {
        $instance = new static(new DictChromeExGoogleTranslator(new CurlClient()));
        $instance->setLanguages($languages);
        
        return $instance;
    }
    
    public function setLanguages(array $languages): void
    {
        $this->languages = [];

        if (empty($languages)) return;

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
            fn ($text, $languagePair) => $this->translator->translate($text, ... $languagePair),
            $text
        );
    }
}