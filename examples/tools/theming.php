<?php

declare(strict_types=1);

/*
 * Theming: a custom palette restyles the whole design system.
 *
 *   php examples/tools/theming.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Document\Document;
use Simtabi\Laranail\Console\Tools\Theme\Theme;

$theme = Theme::make(['primary' => '#ec4899', 'accent' => '#22d3ee', 'success' => '#84cc16']);

echo new Document(null, $theme)
    ->h1('Custom theme')
    ->paragraph('Headings, lists and code all follow the palette.')
    ->bulletList(['pink primary', 'cyan accent'])
    ->codeBlock('echo "themed";')
    ->render(), "\n\n";

// Built-in presets: dracula, nord, solarized, monochrome, github.
foreach (['nord', 'dracula', 'github'] as $preset) {
    echo new Document(null, Theme::preset($preset))->h2(ucfirst($preset) . ' preset')->render(), "\n";
}
