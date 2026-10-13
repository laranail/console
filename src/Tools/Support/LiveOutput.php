<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * Resolves the console an output can redraw in place on, seeing through Laravel's command wrapper.
 *
 * `Illuminate\Console\Command::run()` wraps the terminal's `ConsoleOutput` in an `OutputStyle`
 * before `handle()` runs, and `OutputStyle` is not a {@see ConsoleOutputInterface}, so a bare
 * `instanceof` check sends every command's live rendering down the static path. This unwraps the
 * style through {@see ErrorOutput::unwrap()} and then requires a decorated output: an undecorated
 * one (`--no-ansi`, a pipe) must not receive cursor-control sequences, which is the same rule
 * Symfony's own `ProgressBar` applies before it overwrites.
 *
 * The TTY check stays with the caller, in {@see Capabilities::isInteractive()}.
 *
 * @internal
 */
final class LiveOutput
{
    public static function of(OutputInterface $output): ?ConsoleOutputInterface
    {
        $inner = ErrorOutput::unwrap($output);

        return $inner instanceof ConsoleOutputInterface && $inner->isDecorated() ? $inner : null;
    }
}
