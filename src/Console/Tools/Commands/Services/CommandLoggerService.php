<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Commands\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Command Logger Service
 *
 * Handles structured logging for console commands.
 * Provides consistent logging patterns and context management.
 */
class CommandLoggerService
{
    /**
     * Additional context data
     */
    protected array $context = [];

    public function __construct(
        /**
         * Command name for context
         */
        protected string $commandName = ''
    ) {}

    /**
     * Set command name for logging context
     */
    public function setCommandName(string $commandName): self
    {
        $this->commandName = $commandName;

        return $this;
    }

    /**
     * Add context data
     */
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;

        return $this;
    }

    /**
     * Add multiple context entries
     */
    public function addContextMany(array $context): self
    {
        $this->context = array_merge($this->context, $context);

        return $this;
    }

    /**
     * Get current context
     */
    public function getContext(): array
    {
        return array_merge([
            'command' => $this->commandName,
            'timestamp' => Carbon::now()->toISOString(),
        ], $this->context);
    }

    /**
     * Log command start
     */
    public function logStart(array $additionalContext = []): void
    {
        Log::info('Command started', array_merge($this->getContext(), $additionalContext));
    }

    /**
     * Log command completion
     */
    public function logCompletion(int $exitCode, array $performanceData = [], array $additionalContext = []): void
    {
        $logData = array_merge($this->getContext(), [
            'exit_code' => $exitCode,
            'success' => $exitCode === 0,
        ], $performanceData, $additionalContext);

        if ($exitCode === 0) {
            Log::info('Command completed successfully', $logData);
        } else {
            Log::warning('Command completed with errors', $logData);
        }
    }

    /**
     * Log command error
     */
    public function logError(Throwable $exception, array $additionalContext = []): void
    {
        Log::error('Command execution failed', array_merge($this->getContext(), [
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ], $additionalContext));
    }

    /**
     * Log command warning
     */
    public function logWarning(string $message, array $additionalContext = []): void
    {
        Log::warning($message, array_merge($this->getContext(), $additionalContext));
    }

    /**
     * Log command info
     */
    public function logInfo(string $message, array $additionalContext = []): void
    {
        Log::info($message, array_merge($this->getContext(), $additionalContext));
    }

    /**
     * Log command debug
     */
    public function logDebug(string $message, array $additionalContext = []): void
    {
        Log::debug($message, array_merge($this->getContext(), $additionalContext));
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(array $performanceData, array $additionalContext = []): void
    {
        Log::info('Command performance metrics', array_merge($this->getContext(), $performanceData, $additionalContext));
    }

    /**
     * Log signal received
     */
    public function logSignal(int $signal, array $additionalContext = []): void
    {
        Log::info('Command received termination signal', array_merge($this->getContext(), [
            'signal' => $signal,
        ], $additionalContext));
    }

    /**
     * Log custom event
     */
    public function logEvent(string $eventName, array $eventData = [], array $additionalContext = []): void
    {
        Log::info("Command event: {$eventName}", array_merge($this->getContext(), [
            'event' => $eventName,
            'event_data' => $eventData,
        ], $additionalContext));
    }

    /**
     * Clear context
     */
    public function clearContext(): self
    {
        $this->context = [];

        return $this;
    }

    /**
     * Get command name
     */
    public function getCommandName(): string
    {
        return $this->commandName;
    }
}
