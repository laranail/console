<?php

declare(strict_types=1);

/*
 * ConsoleUIFormatter: the fluent markup builder -- colour methods, emoji, padding,
 * line height and outer spacing -- written through a real Symfony output.
 *
 *   php examples/tools/formatting.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Console\Output\ConsoleOutput;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

$output = new ConsoleOutput;

// Stringable: cast it where a plain string is declared, or call write($output).
$output->writeln(
    (string) ConsoleUIFormatter::create()
        ->txtColorRed()->bgColorWhite()->bold()
        ->message('✓ Deployed 🚀')
        ->padding(2)
        ->lineHeight(3)
        ->addSpaceBefore(2)
        ->addSpaceAfter(),
);

$output->writeln('');

// Emoji by name, and :shortcodes: in the message, with an ASCII fallback.
ConsoleUIFormatter::create()->txtColorBrightGreen()->icon('rocket')->message('Shipped :tada:')->write($output);

// Any colour Support\Color understands: names, hex, rgb(), hsl(), @N.
ConsoleUIFormatter::create()->fg('#7c3aed')->bgColorSlate()->padding(1)->message('hex on slate')->write($output);

// Tags inside the message are text, unless markup() says otherwise.
ConsoleUIFormatter::create()->txtColorCyan()->message('literal: </> <fg=red>')->write($output);
ConsoleUIFormatter::create()->message('markup: <fg=red>red</> plain')->markup()->write($output);

// Indent with a tab, badge, and raw ANSI for echo.
ConsoleUIFormatter::create()->addSpaceBefore(1, ConsoleUIFormatter::TAB)->addMessage('new')
    ->isBadge(ConsoleUIFormatter::BADGE_STYLE_SUCCESS)->write($output);

echo ConsoleUIFormatter::create()->txtColorOrange()->message('echoed as ANSI')->toAnsi(), "\n";
