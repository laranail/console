<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp84\Rector\MethodCall\DowngradeNewMethodCallWithoutParenthesesRector;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkipPath(__DIR__ . '/vendor')
    ->withPhpSets(php83: true)
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
    ])
    // Keep the codebase parseable on the 8.3 floor: wrap PHP 8.4
    // "new X()->method()" expressions so they don't break older minors.
    ->withRules([
        DowngradeNewMethodCallWithoutParenthesesRector::class,
    ])
    ->withImportNames(removeUnusedImports: true);
