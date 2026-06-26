<?php

declare(strict_types=1);

/*
 * Interactive + live demo. The live animation runs only on a TTY; piped/CI it
 * renders a single static frame (no cursor sequences). The button group prompts
 * only when interactive.
 *
 *   php examples/tools/interactive.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Live;
use Simtabi\Laranail\Console\Tools\Widgets\AnimatedBar;
use Simtabi\Laranail\Console\Tools\Widgets\ButtonGroup;

$interactive = Capabilities::detect()->isInteractive();

// Animate a determinate bar (TTY) or draw the final frame once (piped/CI).
Live::make()->refresh(
    static fn (int $i): string => AnimatedBar::make()->label('Building')->width(40)->fraction($i / 10)->render(),
    11,
    60,
);
echo "\n";

if (! $interactive) {
    echo "[non-interactive: button group skipped]\n";

    return;
}

$choice = ButtonGroup::make(['deploy' => 'Deploy', 'rollback' => 'Rollback', 'cancel' => 'Cancel'])
    ->prompt('Choose an action');

echo "You chose: {$choice}\n";
