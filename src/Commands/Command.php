<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Commands;

use Illuminate\Console\Command as BaseCommand;
use Override;
use Simtabi\Laranail\ConsoleTools\Commands\Services\CommandServiceManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Enhanced BaseCommand class with comprehensive functionality
 *
 * Merged from both Core and Nails BaseCommand classes with service-based architecture.
 *
 * Features:
 * - Service-based architecture for better separation of concerns
 * - Laravel 12 native console events integration
 * - Signal handling for graceful shutdown
 * - Performance monitoring and memory tracking
 * - Comprehensive error handling and logging
 * - Metadata management
 * - Laravel Prompts integration
 * - Interactive prompts and validation
 * - Enhanced display methods
 * - Configuration access
 * - Testing support with WithConsoleEvents trait
 *
 * @see https://github.com/bmitch/consoleEvents
 * @see https://laravel.com/docs/artisan
 */
abstract class Command extends BaseCommand
{
    /**
     * Command service manager
     */
    protected CommandServiceManager $services;

    /**
     * Class constructor
     */
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
            $this->services->handleException($e);
            $this->handleException($e);
            $exitCode = 1;
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
        // Handle SIGTERM and SIGINT for graceful shutdown
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

        if ($this->option('verbose')) {
            $this->line("File: {$e->getFile()}:{$e->getLine()}");
            $this->line("Trace: {$e->getTraceAsString()}");
        }
    }

    // ========================================
    // Service Access Methods
    // ========================================

    /**
     * Get the service manager instance
     */
    public function getServices(): CommandServiceManager
    {
        return $this->services;
    }

    /**
     * Configure service settings
     */
    public function configureServices(array $config): self
    {
        $this->services->configure($config);

        return $this;
    }

    // ========================================
    // Performance Methods (Delegate to Services)
    // ========================================

    /**
     * Get command execution time in seconds
     */
    public function getExecutionTime(): float
    {
        return $this->services->performance()->getExecutionTime();
    }

    /**
     * Get formatted execution time
     */
    public function getFormattedExecutionTime(): string
    {
        return $this->services->performance()->getFormattedExecutionTime();
    }

    /**
     * Get memory usage information
     */
    public function getMemoryUsage(): array
    {
        return $this->services->performance()->getMemoryUsage();
    }

    /**
     * Get command performance summary
     */
    public function getPerformanceSummary(): array
    {
        return $this->services->performance()->getPerformanceSummary(
            $this->getCommandName(),
            $this->services->metadata()->all()
        );
    }

    /**
     * Display performance summary
     */
    protected function displayPerformanceSummary(): void
    {
        $summary = $this->getPerformanceSummary();

        $this->info('Command Performance Summary:');
        $this->line("  Command: {$summary['command']}");
        $this->line("  Execution Time: {$summary['execution_time']}");
        $this->line("  Peak Memory: {$summary['memory_usage']['peak_memory']}");

        if (! empty($summary['metadata'])) {
            $this->line('  Metadata: ' . json_encode($summary['metadata']));
        }
    }

    // ========================================
    // Metadata Methods (Delegate to Services)
    // ========================================

    /**
     * Add metadata to the command
     */
    public function addMetadata(string $key, mixed $value): self
    {
        $this->services->metadata()->add($key, $value);

        return $this;
    }

    /**
     * Get metadata value
     */
    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return $this->services->metadata()->get($key, $default);
    }

    /**
     * Get all metadata
     */
    public function getAllMetadata(): array
    {
        return $this->services->metadata()->all();
    }

    // ========================================
    // Signal Handling Methods (Delegate to Services)
    // ========================================

    /**
     * Check if command should continue running
     */
    public function shouldKeepRunning(): bool
    {
        return $this->services->signals()->shouldKeepRunning();
    }

    /**
     * Stop the command gracefully
     */
    public function stop(): void
    {
        $this->services->signals()->stop();
    }

    // ========================================
    // Event Methods (Delegate to Services)
    // ========================================

    /**
     * Enable or disable Laravel native events
     */
    public function useNativeEvents(bool $enabled = true): self
    {
        $this->services->events()->useNativeEvents($enabled);

        return $this;
    }

    /**
     * Enable or disable custom events
     */
    public function useCustomEvents(bool $enabled = true): self
    {
        $this->services->events()->useCustomEvents($enabled);

        return $this;
    }

    // ========================================
    // Configuration Methods (Delegate to Services)
    // ========================================

    /**
     * Get environment value via config
     */
    protected function getEnvValue(string $key, mixed $default = null): mixed
    {
        return $this->services->config()->getEnv($key, $default);
    }

    // ========================================
    // Error Handling Methods (Delegate to Services)
    // ========================================

    /**
     * Log an error with context
     */
    protected function logError(Throwable $e, array $context = []): void
    {
        $this->services->error()->logError($e, $context);
    }

    /**
     * Execute a callback with error handling
     */
    protected function executeWithErrorHandling(callable $callback, string $operation = 'operation'): mixed
    {
        return $this->services->error()->executeWithErrorHandling($callback, $operation);
    }

    /**
     * Execute a callback with error handling and return default on failure
     */
    protected function executeWithFallback(callable $callback, mixed $fallback = null, string $operation = 'operation'): mixed
    {
        return $this->services->error()->executeWithFallback($callback, $fallback, $operation);
    }

    // ========================================
    // Display Methods (Delegate to Services)
    // ========================================

    /**
     * Display a warning message
     */
    protected function warning(string $message): void
    {
        $this->services->display()->warning($message);
    }

    /**
     * Display an error message
     */
    protected function errorMessage(string $message): void
    {
        $this->services->display()->error($message);
    }

    /**
     * Display an info message
     */
    protected function infoMessage(string $message): void
    {
        $this->services->display()->info($message);
    }

    /**
     * Show a progress bar for long operations
     */
    protected function showProgressBar(int $total, string $title = 'Processing'): ProgressBar
    {
        return $this->services->display()->showProgressBar($total, $title);
    }

    // ========================================
    // Interaction Methods (Delegate to Services)
    // ========================================

    /**
     * Show a spinner for operations with unknown duration
     */
    protected function showSpinner(string $message, callable $callback): mixed
    {
        return $this->services->interaction()->showSpinner($message, $callback);
    }

    /**
     * Ask for text input with Laravel Prompts
     */
    protected function askText(string $label, string $placeholder = '', string $default = '', bool $required = false): string
    {
        return $this->services->interaction()->askText($label, $placeholder, $default, $required);
    }

    /**
     * Ask for password input with Laravel Prompts
     */
    protected function askPassword(string $label, string $placeholder = ''): string
    {
        return $this->services->interaction()->askPassword($label, $placeholder);
    }

    /**
     * Ask for confirmation with Laravel Prompts
     */
    protected function askConfirm(string $label, bool $default = false): bool
    {
        return $this->services->interaction()->askConfirm($label, $default);
    }

    /**
     * Ask for selection with Laravel Prompts
     */
    protected function askSelect(string $label, array $options, int $default = 0): string
    {
        return $this->services->interaction()->askSelect($label, $options, $default);
    }

    /**
     * Ask for multiple selections with Laravel Prompts
     */
    protected function askMultiSelect(string $label, array $options, array $default = []): array
    {
        return $this->services->interaction()->askMultiSelect($label, $options, $default);
    }

    /**
     * Confirm an action with the user
     */
    protected function confirmAction(string $question, bool $default = false): bool
    {
        return $this->services->interaction()->confirmAction($question, $default);
    }

    /**
     * Ask for user input with validation
     */
    protected function askWithValidation(string $question, ?callable $validator = null, mixed $default = null): mixed
    {
        return $this->services->interaction()->askWithValidation($question, $validator, $default);
    }

    /**
     * Show a loading message with spinner
     */
    protected function showLoading(string $message, callable $callback): mixed
    {
        return $this->services->interaction()->showLoading($message, $callback);
    }

    // ========================================
    // Utility Methods
    // ========================================

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
