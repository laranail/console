<?php

declare(strict_types=1);

/*
 * Typography components: headings, paragraphs (wrap/justify), lists, quotes, code.
 *
 *   php examples/tools/typography.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Typography\BlockQuote;
use Simtabi\Laranail\Console\Tools\Typography\Code;
use Simtabi\Laranail\Console\Tools\Typography\Heading;
use Simtabi\Laranail\Console\Tools\Typography\ListBlock;
use Simtabi\Laranail\Console\Tools\Typography\Paragraph;

echo Heading::make('Typography', 1)->render(), "\n\n";
echo Paragraph::make(str_repeat('The quick brown fox jumps over the lazy dog. ', 4))->width(60)->align('justify')->render(), "\n\n";
echo Heading::make('Lists', 2)->render(), "\n";
echo ListBlock::make(['unordered one', 'unordered two'])->render(), "\n";
echo ListBlock::make(['first', 'second'])->ordered()->render(), "\n";
echo ListBlock::make()->tasks(['done' => true, 'pending' => false])->render(), "\n";
echo ListBlock::make()->definition(['Term' => 'A definition for the term.'])->render(), "\n\n";
echo BlockQuote::make('A quoted passage that wraps to the available width when it gets long enough to need it.')->width(60)->render(), "\n\n";
echo 'Inline ' . Code::make('code()')->render() . " sits in a sentence.\n";
