<?php

namespace Profile\Text;

interface SinonymizerInterface
{
    public function sinonymize(array $texts): array;
}