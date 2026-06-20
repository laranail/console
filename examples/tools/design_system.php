<?php

declare(strict_types=1);

/*
 * Runnable demo of the v0.5 design system: theme, typography, document, badges,
 * pills, bar chart, banner themes, fluent text/colour. No Laravel app required.
 *
 *   php examples/tools/design_system.php
 *
 * Everything degrades gracefully when piped / non-Unicode / NO_COLOR.
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Document\Document;
use Simtabi\Laranail\Console\Tools\Document\Markdown;
use Simtabi\Laranail\Console\Tools\Typography\Text;
use Simtabi\Laranail\Console\Tools\Widgets\Badge;
use Simtabi\Laranail\Console\Tools\Widgets\Banner;
use Simtabi\Laranail\Console\Tools\Widgets\BarChart;
use Simtabi\Laranail\Console\Tools\Widgets\Pill;

echo Banner::success('DESIGN SYSTEM')->render(), "\n\n";

echo Document::make()
    ->h1('Typography')
    ->paragraph('Compose headings, paragraphs, lists, quotes, links and code into one themed, responsive document. Long prose wraps to the terminal width automatically.')
    ->h2('Lists')
    ->bulletList(['Colours degrade truecolor → 256 → 16 → none', 'Glyphs degrade unicode → ascii'])
    ->orderedList(['First', 'Second', 'Third'])
    ->taskList(['Shipped' => true, 'Documented' => true, 'Celebrated' => false])
    ->h2('Quote & code')
    ->blockQuote('Make the easy things easy and the hard things possible.')
    ->codeBlock("Console::document()->h1('Hi')->paragraph('...')->render();")
    ->render(), "\n\n";

echo Text::make('Inline: ')->text('success ')->success()->render()
, Badge::success('OK')->render(), ' '
, Badge::warning('WARN')->render(), ' '
, Badge::danger('FAIL')->render(), ' '
, Pill::make('beta', 'info')->render(), "\n\n";

echo BarChart::make(['api' => 1240, 'web' => 860, 'cli' => 320, 'cron' => 90])->render(), "\n\n";

echo Markdown::make("# Markdown\n\nRender a **subset** of markdown:\n\n- lists\n- > quotes\n- `code`\n")->render(), "\n";
