<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Facades;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Console\ConsoleManager;
use Simtabi\Laranail\Console\Prompter\Prompter;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Color;
use Simtabi\Laranail\Console\Tools\Widgets\Banner;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\Gauge;
use Simtabi\Laranail\Console\Tools\Widgets\ProgressBar;
use Simtabi\Laranail\Console\Tools\Widgets\Rule;
use Simtabi\Laranail\Console\Tools\Widgets\Sparkline;
use Simtabi\Laranail\Console\Tools\Widgets\Spinner;
use Simtabi\Laranail\Console\Tools\Widgets\StatusLine;
use Simtabi\Laranail\Console\Tools\Widgets\StepFlow;
use Simtabi\Laranail\Console\Tools\Widgets\Table;
use Simtabi\Laranail\Console\Tools\Widgets\TaskProgress\TaskProgress;
use Simtabi\Laranail\Console\Tools\Widgets\Tree;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method static ConsoleUIFormatter ui()
 * @method static Spinner spinner(string $message = '')
 * @method static ProgressBar progress(?OutputInterface $output = null, int $max = 0)
 * @method static StatusLine status()
 * @method static Rule rule(string $title = '')
 * @method static Box box(array|string $content = [])
 * @method static Tree tree(string $label = '')
 * @method static Table table()
 * @method static Gauge gauge(float $value, float $max = 100.0)
 * @method static Sparkline sparkline(array $values)
 * @method static Banner banner(string $title)
 * @method static StepFlow steps(array $steps = [])
 * @method static TaskProgress tasks(?OutputInterface $output = null)
 * @method static Color color()
 * @method static Capabilities capabilities()
 * @method static Prompter prompter()
 *
 * @see ConsoleManager
 */
final class Console extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConsoleManager::class;
    }
}
