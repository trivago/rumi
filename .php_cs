<?php

return Symfony\CS\Config\Config::create()
    ->finder(Symfony\CS\Finder\DefaultFinder::create()->in(__DIR__ . '/src'))
    ->setUsingCache(true)
    ->fixers([
        'concat_with_spaces',
        'ordered_use',
    ])
;
