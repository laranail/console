<?php

declare(strict_types=1);

/*
 * Render a Markdown subset to the terminal: headings, paragraphs, lists, task
 * lists, quotes, tables, and fenced code (php/json/bash/yaml/js highlighting).
 * Inline **bold** / *italic* / `code` / [links](url) render in paragraphs, list
 * items and quotes alike. Degrades gracefully when piped / NO_COLOR.
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

    - **Typography** & documents
    - Colour with graceful `downgrade`
    - [x] Responsive, with [inline links](https://opensource.simtabi.com)
    - [ ] World domination

    > Make the easy things **easy** and the hard things `possible`.

    ## Output styles

    | Style | When |
    | --- | --- |
    | truecolor | modern terminals |
    | ansi-16 | basic / CI |
    | plain | piped / NO_COLOR |

    ```bash
    composer require laranail/console # install
    ```

    ```php
    return Console::document()->h1('Hi')->paragraph('...')->render();
    ```
    MD;

echo Markdown::make($md)->render(), "\n";
