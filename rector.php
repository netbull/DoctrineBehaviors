<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()->withPaths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/utils'])
    ->withComposerBased(doctrine: true)
    ->withImportNames()
    ->withParallel()
    ->withSkip([
        RenamePropertyToMatchTypeRector::class => [__DIR__ . '/tests/ORM/'],
    ])
    ->withSets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::NAMING,
        LevelSetList::UP_TO_PHP_80,
    ]);
