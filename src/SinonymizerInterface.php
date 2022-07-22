<?php

namespace Profile\Text\Sinonymizer;

interface SinonymizerInterface
{
    public function sinonymize(string $text): string;
}