<?php

declare(strict_types=1);

/*
 * Interactive Menu demo. Unlike examples/tools/widgets.php this one reads keys,
 * so it is NOT part of the CI smoke set. It self-guards: on a non-TTY it prints
 * a note and exits 0 instead of blocking.
 *
 *   php examples/tools/menu.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Keypress;
use Simtabi\Laranail\Console\Tools\Widgets\Menu\Menu;

if (! Capabilities::detect()->isInteractive() || ! Keypress::isSupported()) {
    fwrite(STDOUT, "menu.php needs an interactive TTY; skipping (would fall back to prompts in real use).\n");
    exit(0);
}

$choice = Menu::make('Pizza menu', ['Margherita', 'Pepperoni', 'Veggie'])
    ->addStaticItem('— extras —')
    ->addCheckbox('cheese', 'Extra cheese')
    ->addQuestion('Custom', 'describe your pizza…')
    ->setExitButtonText('Quit')
    ->open();

fwrite(STDOUT, 'You chose: ' . var_export($choice, true) . "\n");

exit(0);
