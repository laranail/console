<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Commands;

use Illuminate\Console\Command as BaseCommand;
use Override;
use Simtabi\Laranail\Console\Tools\Commands\Services\CommandServiceManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Enhanced Artisan command base. Wraps {@see BaseCommand::run()} with a managed
 * lifecycle — performance timing, event dispatch, signal handling, structured
 * logging and exception capture — coordinated by a {@see CommandServiceManager}.
 *
 * This base is intentionally thin: it owns the lifecycle and a few verbosity
 * helpers, and exposes everything else through `$this->services` (its nine
 * discrete services), e.g. `$this->services->metadata()->add(...)`,
 * `$this->services->interaction()->askText(...)`,
 * `$this->services->performance()->getExecutionTime()`. Extend it when you want
 * the full lifecycle; for a lightweight prompter command use
 * `Prompter\Commands\AbstractPrompterCommand` instead.
 *
 * @see https://laravel.com/docs/artisan
 */
abstract class Command extends BaseCommand
{
    /**
     * Command service manager — the access point for every command service.
     */
    protected CommandServiceManager $services;

    public function __construct()
    {
        parent::__construct();

        // Initialize service manager
        $this->services = new CommandServiceManager($this->getCommandName());

        // Set up signal handling for graceful shutdown
        $this->setupSignalHandling();
    }

    /**
     * Override the run method to add event dispatching and timing
     */
    #[Override]
    public function run(InputInterface $input, OutputInterface $output): int
    {
        // Set output for display service
        $this->services->setOutput($output);

        // Start command execution
        $this->services->startCommand();

        // Configure non-interactive mode. Read interactivity from the passed
        // $input: the application sets it before run() is invoked, whereas
        // $this->input is not populated until parent::run() binds it below.
        $this->services->configure([
            'non_interactive' => ! $input->isInteractive(),
        ]);

        // Dispatch starting events
        $this->services->events()->dispatchStarting(
            $this->getCommandName(),
            $input,
            $output,
            $this->services->metadata()->all()
        );

        $exitCode = self::FAILURE;

        try {
            $exitCode = parent::run($input, $output);
        } catch (Throwable $e) {
            // Structured, redacted logging (single source of truth) plus a
            // user-facing message. The command exits non-zero rather than
            // bubbling a raw stack trace to the terminal.
            $this->services->handleException($e);
            $this->handleException($e);
            $exitCode = self::FAILURE;
        } finally {
            // End command execution
            $this->services->endCommand($exitCode);

            // Dispatch finished events
            $this->services->events()->dispatchFinished(
                $this->getCommandName(),
                $input,
                $output,
                $exitCode,
                $this->services->metadata()->all()
            );
        }

        return $exitCode;
    }

    /**
     * Set up signal handling for graceful shutdown
     */
    protected function setupSignalHandling(): void
    {
        // Signal handling requires ext-pcntl; the SIGTERM/SIGINT constants are
        // only defined when it is loaded (e.g. not on Windows). Bail out early
        // so constructing a command never fatals on platforms without it.
        if (! extension_loaded('pcntl')) {
            return;
        }

        // Handle SIGTERM and SIGINT for graceful shutdown.
        $this->trap([SIGTERM, SIGINT], function (int $signal): void {
            $this->info('Received termination signal. Gracefully shutting down...');
            $this->services->signals()->stop();

            // Log the signal received
            $this->services->logger()->logSignal($signal, [
                'execution_time' => $this->services->performance()->getFormattedExecutionTime(),
                'memory_usage' => $this->services->performance()->getMemoryUsage(),
            ]);
        });
    }

    /**
     * Handle exceptions during command execution
     */
    protected function handleException(Throwable $e): void
    {
        $this->error("Command failed: {$e->getMessage()}");

        // File/line on -v; the full stack trace only on -vvv (debug). Traces can
        // carry sensitive call arguments, so they are not shown at lower levels.
        if ($this->isVerbose()) {
            $this->line("File: {$e->getFile()}:{$e->getLine()}");
        }

        if ($this->isDebug()) {
            $this->line("Trace: {$e->getTraceAsString()}");
        }
    }

    /**
     * Get the service manager — the access point for every command service
     * (performance, events, signals, metadata, logger, error, config,
     * interaction, display).
     */
    public function getServices(): CommandServiceManager
    {
        return $this->services;
    }

    /**
     * Configure service settings (native_events / custom_events / signals /
     * non_interactive).
     */
    public function configureServices(array $config): self
    {
        $this->services->configure($config);

        return $this;
    }

    /**
     * Display a performance summary for the current command via the display
     * service.
     */
    protected function displayPerformanceSummary(): void
    {
        $summary = $this->services->performance()->getPerformanceSummary(
            $this->getCommandName(),
            $this->services->metadata()->all()
        );

        $this->info('Command Performance Summary:');
        $this->line("  Command: {$summary['command']}");
        $this->line("  Execution Time: {$summary['execution_time']}");
        $this->line("  Peak Memory: {$summary['memory_usage']['peak_memory']}");

        if (! empty($summary['metadata'])) {
            $this->line('  Metadata: ' . json_encode($summary['metadata']));
        }
    }

    /**
     * Get command name for logging purposes
     */
    protected function getCommandName(): string
    {
        return $this->getName() ?? class_basename(static::class);
    }

    /**
     * Get the underlying console output (for verbosity checks).
     */
    protected function getCliOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * Check if verbose output is enabled
     */
    protected function isVerbose(): bool
    {
        return $this->getCliOutput()->isVerbose();
    }

    /**
     * Check if very verbose output is enabled
     */
    protected function isVeryVerbose(): bool
    {
        return $this->getCliOutput()->isVeryVerbose();
    }

    /**
     * Check if debug output is enabled
     */
    protected function isDebug(): bool
    {
        return $this->getCliOutput()->isDebug();
    }

    /**
     * Check if a command should run in non-interactive mode
     */
    protected function isNonInteractive(): bool
    {
        if ($this->option('no-interaction')) {
            return true;
        }

        return (bool) $this->option('force');
    }

    /**
     * Execute a command with error handling
     */
    protected function executeCommand(string $command, array $arguments = [], bool $throwOnError = true): int
    {
        try {
            return $this->call($command, $arguments);
        } catch (Throwable $e) {
            if ($throwOnError) {
                throw $e;
            }

            return Command::FAILURE;
        }
    }
}
