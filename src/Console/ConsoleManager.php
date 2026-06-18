<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console;

use Simtabi\Laranail\Console\Prompter\Prompter;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Color;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\ProgressBar;
use Simtabi\Laranail\Console\Tools\Widgets\Rule;
use Simtabi\Laranail\Console\Tools\Widgets\Spinner;
use Simtabi\Laranail\Console\Tools\Widgets\StatusLine;
use Simtabi\Laranail\Console\Tools\Widgets\Tree;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Truecolor / hex / gradient colouring.
     */
    public function color(): Color
    {
        return Color::make();
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
        return Prompter::getInstance();
    }
}
