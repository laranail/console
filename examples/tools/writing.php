<?php

declare(strict_types=1);

/*
 * ConsoleWriter: a fluent output writer with context statuses and emoji.
 *
 *   php examples/tools/writing.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Services\ConsoleWriter;
use Symfony\Component\Console\Output\ConsoleOutput;

$w = ConsoleWriter::make(new ConsoleOutput);

// styling (fluent + immutable)
$w->color('cyan')->bold()->line('ConsoleWriter');
$w->style('comment')->line('a fluent, immutable output writer');
$w->newLine();

// context statuses (coloured glyph + message; error/danger → stderr)
$w->success('Deployed to production');
$w->info('Warming caches…');
$w->note('Heads up: this is informational');
$w->warning('Low disk space');
$w->pending('3 jobs queued');
$w->error('A step failed');
$w->newLine();

// emoji & symbols
$w->emoji('rocket')->line('Launching the app');
$w->emoji(':sparkles:')->line('Tidied up');
$w->symbol('arrow')->line('Next step');
$w->line('Inline shortcodes render too :tada:');
