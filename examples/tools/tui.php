<?php

declare(strict_types=1);

/*
 * Full-screen symfony/tui demo: mounts our widgets into a TUI app.
 *
 * Interactive (boots an event loop), so it is NOT part of the CI smoke set and
 * self-guards on a non-TTY. Requires PHP >= 8.4.1 + symfony/tui.
 *
 *   php examples/tools/tui.php        (press q / Ctrl-C to quit)
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\Table;
use Simtabi\Laranail\Console\Tui\RenderableWidget;
use Symfony\Component\Tui\Tui;

if (! Capabilities::detect()->isInteractive()) {
    fwrite(STDOUT, "tui.php needs an interactive TTY; skipping.\n");
    exit(0);
}

$tui = new Tui;
$tui->add(RenderableWidget::of(
    Box::make(['Welcome to laranail/console', 'Press q or Ctrl-C to quit'])->title('TUI demo')->rounded()
));
$tui->add(RenderableWidget::of(
    Table::make()->fromAssoc([
        ['widget' => 'Box', 'mounted' => 'yes'],
        ['widget' => 'Table', 'mounted' => 'yes'],
    ])
));

$tui->run();

exit(0);
