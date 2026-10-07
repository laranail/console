<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * Resolves the stderr stream behind an output, seeing through Laravel's command wrapper.
 *
 * `Illuminate\Console\Command::run()` wraps the terminal's `ConsoleOutput` in an
 * {@see OutputStyle} before Symfony calls `initialize()` or `handle()`, and `OutputStyle` does
 * not implement {@see ConsoleOutputInterface}. A bare `$output instanceof ConsoleOutputInterface`
 * check is therefore false for every output a command hands out, and anything meant for stderr
 * lands on stdout. This unwraps the style first.
 *
 * An output with no separate error stream (a `BufferedOutput`, as `Artisan::call()` uses) is
 * returned unchanged, so captured output still contains everything.
 *
 * @internal
 */
final class ErrorOutput
{
    public static function of(OutputInterface $output): OutputInterface
    {
        $inner = $output instanceof OutputStyle ? $output->getOutput() : $output;

        return $inner instanceof ConsoleOutputInterface ? $inner->getErrorOutput() : $output;
    }
}
