<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console;

use Simtabi\Laranail\Console\Prompter\Prompter;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Color;
use Simtabi\Laranail\Console\Tools\Support\Emoji;
use Simtabi\Laranail\Console\Tools\Support\Keypress;
use Simtabi\Laranail\Console\Tools\Support\Terminal;
use Simtabi\Laranail\Console\Tools\Widgets\Banner;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\Columns;
use Simtabi\Laranail\Console\Tools\Widgets\Gauge;
use Simtabi\Laranail\Console\Tools\Widgets\Menu\Menu;
use Simtabi\Laranail\Console\Tools\Widgets\Panel;
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
use Symfony\Component\Tui\Tui;

/**
 * Unified entry point for the laranail/console package.
 *
 * A thin aggregator: each accessor returns the real domain object (with its
 * full fluent API), so this class never proxies methods. It is the single
 * place the Tools and Prompter sub-domains meet — the sub-domains themselves
 * stay decoupled.
 */
final class ConsoleManager
{
    /**
     * Console output toolkit (formatter, badges, status lines).
     */
    public function ui(): ConsoleUIFormatter
    {
        return ConsoleUIFormatter::create();
    }

    /**
     * A fluent activity spinner.
     */
    public function spinner(string $message = ''): Spinner
    {
        return Spinner::make($message);
    }

    /**
     * A flavoured progress bar (percent / elapsed / ETA / rate).
     */
    public function progress(?OutputInterface $output = null, int $max = 0): ProgressBar
    {
        return ProgressBar::make($output, $max);
    }

    /**
     * One-line status messages with coloured glyph prefixes.
     */
    public function status(): StatusLine
    {
        return StatusLine::make();
    }

    /**
     * A full-width horizontal divider with an optional inline title.
     */
    public function rule(string $title = ''): Rule
    {
        return Rule::make($title);
    }

    /**
     * Frame text in a box.
     *
     * @param list<string>|string $content
     */
    public function box(array|string $content = []): Box
    {
        return Box::make($content);
    }

    /**
     * Build a nested tree.
     */
    public function tree(string $label = ''): Tree
    {
        return Tree::make($label);
    }

    /**
     * Build a data table.
     */
    public function table(): Table
    {
        return Table::make();
    }

    /**
     * Flow a flat list of items into balanced columns.
     *
     * @param list<string> $items
     */
    public function columns(array $items): Columns
    {
        return Columns::make($items);
    }

    /**
     * A single-value gauge/meter.
     */
    public function gauge(float $value, float $max = 100.0): Gauge
    {
        return Gauge::make($value, $max);
    }

    /**
     * An inline block-eighths sparkline.
     *
     * @param list<int|float> $values
     */
    public function sparkline(array $values): Sparkline
    {
        return Sparkline::make($values);
    }

    /**
     * A centred start-of-run banner/masthead.
     */
    public function banner(string $title): Banner
    {
        return Banner::make($title);
    }

    /**
     * A wizard/pipeline breadcrumb (done / current / pending).
     *
     * @param list<string> $steps
     */
    public function steps(array $steps = []): StepFlow
    {
        return StepFlow::make($steps);
    }

    /**
     * A multi-task progress widget (exit non-zero on any failure).
     */
    public function tasks(?OutputInterface $output = null): TaskProgress
    {
        return TaskProgress::make($output);
    }

    /**
     * Truecolor / hex / gradient colouring.
     */
    public function color(): Color
    {
        return Color::make();
    }

    /**
     * Emoji helper — Unicode or ASCII, configurable per call (auto/unicode/ascii).
     */
    public function emoji(): Emoji
    {
        return Emoji::make();
    }

    /**
     * A multi-block layout (vertical/horizontal, nestable).
     */
    public function panel(): Panel
    {
        return Panel::make();
    }

    /**
     * A native interactive menu (key-driven on a TTY, prompts fallback otherwise).
     *
     * @param array<int|string, string>|list<string> $options
     */
    public function menu(string $title = '', array $options = []): Menu
    {
        return Menu::make($title, $options);
    }

    /**
     * Low-level terminal control (bell, tab title, alt-screen, cursor/erase).
     */
    public function terminal(?OutputInterface $output = null): Terminal
    {
        return Terminal::make($output);
    }

    /**
     * Raw key/arrow reader (POSIX TTY; degrades gracefully elsewhere).
     */
    public function keypress(): Keypress
    {
        return Keypress::make();
    }

    /**
     * A symfony/tui full-screen app. Mount our widgets with
     * Console\Tui\RenderableWidget::of(...). Requires symfony/tui.
     */
    public function tui(): Tui
    {
        return new Tui;
    }

    /**
     * Detected terminal capabilities (TTY, colour, Unicode, width).
     */
    public function capabilities(): Capabilities
    {
        return Capabilities::detect();
    }

    /**
     * Interactive input toolkit (prompts, forms, validators).
     */
    public function prompter(): Prompter
    {
        return Prompter::create();
    }
}
