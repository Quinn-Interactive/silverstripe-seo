<?php

declare(strict_types=1);

use Cambis\SilverstripeRector\Set\ValueObject\SilverstripeLevelSetList;
use Cambis\SilverstripeRector\Set\ValueObject\SilverstripeSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(php82: true)
    ->withImportNames(importShortClasses: false)
    ->withSets([
        SilverstripeLevelSetList::UP_TO_SILVERSTRIPE_54,
        SilverstripeSetList::CODE_QUALITY,
    ]);
