<?php

namespace Profile\Text;

use Profile\Text\Translator\Google\GtxGoogleTranslator;

class TranslatorSinonymizer extends GtxGoogleTranslator implements SinonymizerInterface
{
    public function sinonymize(array $texts): array
    {
        return $this->translate(
            array_filter(
                $this->translate($texts, 'ru', 'en'),
                static fn(string $text): bool => is_string($text)
            ),
            'en',
            'ru'
        );
    }
}