<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Facades;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Console\ConsoleManager;
use Simtabi\Laranail\Console\Prompter\Prompter;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Color;
use Simtabi\Laranail\Console\Tools\Support\Emoji;
use Simtabi\Laranail\Console\Tools\Support\Keypress;
use Simtabi\Laranail\Console\Tools\Support\Os;
use Simtabi\Laranail\Console\Tools\Support\Style;
use Simtabi\Laranail\Console\Tools\Support\Terminal;
use Simtabi\Laranail\Console\Tools\Widgets\Banner;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\Columns;
use Simtabi\Laranail\Console\Tools\Widgets\Gauge;
use Simtabi\Laranail\Console\Tools\Widgets\Header;
use Simtabi\Laranail\Console\Tools\Widgets\KeyValue;
use Simtabi\Laranail\Console\Tools\Widgets\Menu\Menu;
use Simtabi\Laranail\Console\Tools\Widgets\Panel;
use Simtabi\Laranail\Console\Tools\Widgets\ProgressBar;
use Simtabi\Laranail\Console\Tools\Widgets\Rule;
use Simtabi\Laranail\Console\Tools\Widgets\Sparkline;
use Simtabi\Laranail\Console\Tools\Widgets\Spinner;
use Simtabi\Laranail\Console\Tools\Widgets\StatusLine;
use Simtabi\Laranail\Console\Tools\Widgets\StepFlow;
use Simtabi\Laranail\Console\Tools\Widgets\Summary;
use Simtabi\Laranail\Console\Tools\Widgets\Table;
use Simtabi\Laranail\Console\Tools\Widgets\TaskProgress\TaskProgress;
use Simtabi\Laranail\Console\Tools\Widgets\Tree;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Tui\Tui;

/**
 * @method static ConsoleUIFormatter ui()
 * @method static Spinner spinner(string $message = '')
 * @method static ProgressBar progress(?OutputInterface $output = null, int $max = 0)
 * @method static StatusLine status()
 * @method static Rule rule(string $title = '')
 * @method static Box box(array|string $content = [])
 * @method static Tree tree(string $label = '')
 * @method static Table table()
 * @method static Columns columns(array $items)
 * @method static KeyValue keyValue(array $pairs = [])
 * @method static Gauge gauge(float $value, float $max = 100.0)
 * @method static Sparkline sparkline(array $values)
 * @method static Banner banner(string $title)
 * @method static Header header(string $title)
 * @method static Summary summary(array $stats, string $title = 'EXECUTION SUMMARY')
 * @method static StepFlow steps(array $steps = [])
 * @method static TaskProgress tasks(?OutputInterface $output = null)
 * @method static Color color()
 * @method static Emoji emoji()
 * @method static Style style()
 * @method static string symbol(string $name)
 * @method static Os os()
 * @method static Panel panel()
 * @method static Menu menu(string $title = '', array $options = [])
 * @method static Terminal terminal(?OutputInterface $output = null)
 * @method static Keypress keypress()
 * @method static Tui tui()
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
