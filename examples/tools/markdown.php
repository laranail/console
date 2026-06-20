<?php

declare(strict_types=1);

/*
 * Render a Markdown subset to the terminal: headings, lists, task lists, quotes,
 * fenced code (php/json highlighting), and inline **bold** / *italic* / `code` /
 * [links](url). Degrades gracefully when piped / NO_COLOR.
 *
 *   php examples/tools/markdown.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Document\Markdown;

$md = <<<'MD'
    # laranail/console

    A **design system** for the terminal with *themeable* output and `inline code`.
    See the [docs](https://opensource.simtabi.com/console/docs/).

    ## Features

    - Typography & documents
    - Colour with graceful **downgrade**
    - [x] Responsive
    - [ ] World domination

    > Make the easy things easy and the hard things possible.

    ```php
    return Console::document()->h1('Hi')->paragraph('...')->render();
    ```
    MD;

echo Markdown::make($md)->render(), "\n";
