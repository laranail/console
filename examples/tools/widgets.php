<?php

declare(strict_types=1);

/*
 * Runnable demo of the Console\Tools widgets. No Laravel app required.
 *
 *   php examples/tools/widgets.php
 *
 * Formatter/status strings carry Symfony markup, so write them through a
 * Symfony output (colour renders on a TTY, and is stripped when piped).
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Support\Color;
use Simtabi\Laranail\Console\Tools\Widgets\Banner;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\Callout;
use Simtabi\Laranail\Console\Tools\Widgets\Gauge;
use Simtabi\Laranail\Console\Tools\Widgets\ProgressBar;
use Simtabi\Laranail\Console\Tools\Widgets\Rule;
use Simtabi\Laranail\Console\Tools\Widgets\Sparkline;
use Simtabi\Laranail\Console\Tools\Widgets\StatusLine;
use Simtabi\Laranail\Console\Tools\Widgets\StepFlow;
use Simtabi\Laranail\Console\Tools\Widgets\Table;
use Simtabi\Laranail\Console\Tools\Widgets\TaskProgress\TaskProgress;
use Simtabi\Laranail\Console\Tools\Widgets\Tree;
use Symfony\Component\Console\Output\ConsoleOutput;

$out = new ConsoleOutput;

$out->writeln(Banner::make('laranail/console')->subtitle('widget demo')->width(40)->render());
$out->writeln('');

$status = StatusLine::make();
$out->writeln($status->success('Dependencies installed'));
$out->writeln($status->warning('Cache is stale'));
$out->writeln($status->error('Migration failed'));
$out->writeln('');

$out->writeln(Rule::make('CONFIG')->width(40)->render());
$out->writeln(Box::make(['Driver: pgsql', 'Host:   localhost'])->title('Database')->render());
$out->writeln('');

$color = new Color;
$out->writeln($color->fg('Brand colour (#ff8800)', '#ff8800'));
$out->writeln($color->gradient('Gradient welcome banner', ['#00ffff', '#ff00ff']));
$out->writeln('');

$out->writeln(Tree::make('app')
    ->child('Console', fn (Tree $t) => $t->child('Tools')->child('Prompter'))
    ->child('Providers')
    ->render());
$out->writeln('');

$out->writeln(Gauge::make(184, 250)->label('Disk C:')->showValue()->render());
$out->writeln('CPU ' . Sparkline::make([1, 2, 3, 5, 7, 6, 4, 2, 1])->render());
$out->writeln('');

$out->writeln(Callout::info('Run `php artisan migrate` to finish setup.')->title('Next step')->render());
$out->writeln('');

$out->writeln(Table::make()
    ->headers(['Service', 'Status'])
    ->rows([['web', 'up'], ['queue', 'up'], ['cron', 'down']])
    ->style('light')
    ->render());

$out->writeln(StepFlow::make(['Detect', 'Plan', 'Apply', 'Verify'])->current(2)->render());
$out->writeln('');

$bar = ProgressBar::make($out, 30)->format('detailed')->glyphs('blocks');
$bar->start();
for ($i = 0; $i < 30; $i++) {
    usleep(15000);
    $bar->advance();
}
$bar->finish();
$out->writeln("\n");

$tasks = TaskProgress::make($out);
$tasks->task('Compile', 1)->advance(1)->succeed();
$tasks->task('Bundle', 1)->advance(1)->succeed('cached');
$tasks->task('Upload', 1)->fail('network error');
$tasks->finish();

// The demo always exits 0 so it can serve as a CI smoke check; in real use you
// would `exit($tasks->finish())` to propagate a non-zero code on failure.
exit(0);
