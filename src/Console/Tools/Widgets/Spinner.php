<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use function Laravel\Prompts\spin;

use Simtabi\Laranail\Console\Tools\Enums\SpinnerFrames;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Config;
use Simtabi\Laranail\Console\Tools\Support\Symbols;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A fluent activity spinner.
 *
 * Two modes:
 *  - run(): animate while a callback executes, then resolve to a status line.
 *    Delegates animation to Laravel Prompts (which handles the pcntl/non-TTY
 *    fallbacks), so it is safe everywhere.
 *  - start()/advance()/finish(): drive frames manually inside your own loop.
 *
 * Frame set, Unicode and colour all degrade gracefully via {@see Capabilities}.
 */
final class Spinner
{
    private string $message = '';

    private SpinnerFrames $frames;

    private int $index = 0;

    private bool $active = false;

    private readonly OutputInterface $output;

    private readonly Capabilities $capabilities;

    private readonly Symbols $symbols;

    public function __construct(?OutputInterface $output = null, ?Capabilities $capabilities = null)
    {
        $this->output = $output ?? new ConsoleOutput;
        $this->capabilities = $capabilities ?? Capabilities::detect();
        $this->symbols = Symbols::for($this->capabilities);
        $this->frames = SpinnerFrames::fromName((string) Config::get('spinner.frames', 'braille'));
    }

    public static function make(string $message = ''): self
    {
        return (new self)->message($message);
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function frames(SpinnerFrames|string $frames): self
    {
        $this->frames = $frames instanceof SpinnerFrames ? $frames : SpinnerFrames::fromName($frames);

        return $this;
    }

    /**
     * Animate the spinner while $callback runs; return the callback's result.
     *
     * Note: run() delegates animation to Laravel Prompts, which uses its own
     * frame set and TTY handling — so a custom frames() set applies only to the
     * manual start()/advance()/finish() mode, not to run().
     */
    public function run(callable $callback): mixed
    {
        return spin(static fn () => $callback(), $this->message);
    }

    public function start(): self
    {
        $this->active = true;
        $this->index = 0;

        if ($this->capabilities->isInteractive()) {
            (new Cursor($this->output))->hide();
        }

        return $this->advance();
    }

    public function advance(): self
    {
        if (! $this->active) {
            return $this;
        }

        $set = $this->frames->frames($this->capabilities->supportsUnicode());
        $frame = $set[$this->index % count($set)];
        $this->index++;

        if ($this->capabilities->isInteractive()) {
            $this->output->write("\r" . $frame . ' ' . $this->message);
        }

        return $this;
    }

    /**
     * Stop the spinner and print a final status line.
     *
     * @param 'success'|'error'|'warning'|'info' $status
     */
    public function finish(string $status = 'success', ?string $message = null): self
    {
        $this->active = false;

        if ($this->capabilities->isInteractive()) {
            $cursor = new Cursor($this->output);
            $this->output->write("\r");
            $cursor->clearLineAfter();
            $cursor->show();
        }

        $this->output->writeln($this->symbols->get($status) . ' ' . ($message ?? $this->message));

        return $this;
    }
}
