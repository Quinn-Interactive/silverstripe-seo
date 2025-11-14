<?php

declare(strict_types=1);

use Cambis\SilverstripeRector\Set\ValueObject\SilverstripeLevelSetList;
use Cambis\SilverstripeRector\Set\ValueObject\SilverstripeSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(php83: true)
    ->withImportNames(importShortClasses: false)
    ->withSets([
        SilverstripeLevelSetList::UP_TO_SILVERSTRIPE_60,
        SilverstripeSetList::CODE_QUALITY,
    ]);
