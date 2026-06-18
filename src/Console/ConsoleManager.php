<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console;

use Simtabi\Laranail\Console\Prompter\Prompter;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

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
     * Console output toolkit (formatter, badges, status lines, widgets).
     */
    public function ui(): ConsoleUIFormatter
    {
        return ConsoleUIFormatter::create();
    }

    /**
     * Interactive input toolkit (prompts, forms, validators).
     */
    public function prompter(): Prompter
    {
        return Prompter::getInstance();
    }
}
