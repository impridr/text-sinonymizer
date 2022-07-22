<?php

namespace Profile\Text\Sinonymizer;

use \Profile\Http\Client\CurlClient;
use \Profile\Text\Translator\DictChromeExGoogleTranslator;

class TranslatorSinonymizer extends DictChromeExGoogleTranslator implements SinonymizerInterface
{
    protected array $languages;

    public static function create(array $languages): self
    {
        $instance = new static(new CurlClient());
        $instance->languages = $languages;

        return $instance;
    }

    public function sinonymize(string $text): string
    {
        $languages = $this->languages;
        $languages[] = $sourceLanguage = array_shift($languages);

        while ($targetLanguage = array_shift($languages)) {
            $text = $this->translate($text, $sourceLanguage, $targetLanguage);
            $sourceLanguage = $targetLanguage;
        }

        return $text;
    }
}