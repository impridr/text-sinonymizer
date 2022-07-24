<?php

namespace Profile\Text\Sinonymizer;

use \Profile\Http\Client\CurlClient;
use \Profile\Text\Translator\{TranslatorInterface, DictChromeExGoogleTranslator};

class TranslatorSinonymizer implements SinonymizerInterface
{
    protected TranslatorInterface $translator;
    protected array $languages = [];
    protected array $excludes = [];
    protected array $includes = [];

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function create(array $languages = [], array $excludes = []): self
    {
        $instance = new static(new DictChromeExGoogleTranslator(new CurlClient()));
        $instance->setLanguages($languages);
        $instance->setExcludes($excludes);
        
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
    
    public function setExcludes(array $excludes): void
    {
        foreach ($excludes as $pattern)

            if (static::isRegex($pattern))

                $this->excludes[] = $pattern;
    }

    public function withExcludes(array $excludes): self
    {
        $clone = clone($this);
        $clone->setExcludes($excludes);

        return $clone;
    }
    
    public function getExcludes(): array
    {
        return $this->excludes;
    }

    public function exclude(string $text): string
    {
        $replacements = [];

        foreach ($this->getExcludes() as $pattern)
        {
            $matches = [];
            preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

           $replacements = array_merge($replacements, $matches[0]);
        }

        usort($replacements, fn ($a, $b) => $a[1] > $b[1]);

        $this->includes = [];

        foreach ($replacements as $i => $match) {

            $this->includes[$i] = $match[0];
            $text = substr_replace($text, "(%%{$i}%%) {$match[0]} (%%{$i}%%)", $match[1] + (7 + strlen($i)) * 2 * $i, strlen($match[0]));
        }

        return $text;
    }

    public function include(string $text): string
    {
        foreach ($this->includes as $i => $original)
        {
            $matches = [];
            $pattern = "\(%%{$i}%%\)";
            preg_match_all("#{$pattern}.+{$pattern}#u", $text, $matches, PREG_OFFSET_CAPTURE);
            
            foreach ($matches[0] as $i => $match)

                $text = substr_replace($text, $original, $match[1] - (7 + strlen($i)) * 2 * $i, strlen($match[0]));
        }

        return $text;
    }

    public function sinonymize(string $text): string
    {
        return $this->include(
            array_reduce(
                $this->getLanguages(),
                fn ($text, $languagePair) => $this->translator->translate($this->exclude($text), ... $languagePair),
                $text
            )
        );
    }

    public static function isRegex(string $pattern): bool
    {
        set_error_handler(function () {}, E_WARNING);
        $isRegex = false !== preg_match($pattern, '');
        restore_error_handler();

        return $isRegex;
    }
}