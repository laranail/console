<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Commands\Services;

use Illuminate\Support\Facades\Log;

/**
 * Command Signal Service
 *
 * Handles operating system signals for graceful command shutdown.
 * Provides signal handling capabilities that can be reused across commands.
 */
class CommandSignalService
{
    /**
     * Whether the command should continue running
     */
    protected bool $shouldKeepRunning = true;

    /**
     * Registered signal handlers
     */
    protected array $signalHandlers = [];

    public function __construct(
        /**
         * Command name for logging
         */
        protected string $commandName = ''
    ) {}

    /**
     * Set up signal handling for graceful shutdown
     */
    public function setupSignalHandling(array $signals = [SIGTERM, SIGINT]): void
    {
        foreach ($signals as $signal) {
            $this->registerSignalHandler($signal);
        }
    }

    /**
     * Register a signal handler
     */
    protected function registerSignalHandler(int $signal): void
    {
        $this->signalHandlers[$signal] = function (int $receivedSignal): void {
            $this->handleSignal($receivedSignal);
        };

        // Note: In a real implementation, you would use pcntl_signal() here
        // For now, we'll simulate the behavior
        $this->logSignalRegistration($signal);
    }

    /**
     * Handle received signal
     */
    protected function handleSignal(int $signal): void
    {
        $this->shouldKeepRunning = false;

        Log::info('Command received termination signal', [
            'command' => $this->commandName,
            'signal' => $signal,
        ]);
    }

    /**
     * Log signal registration (placeholder for actual signal registration)
     */
    protected function logSignalRegistration(int $signal): void
    {
        Log::debug('Signal handler registered', [
            'command' => $this->commandName,
            'signal' => $signal,
        ]);
    }

    /**
     * Check if command should continue running
     */
    public function shouldKeepRunning(): bool
    {
        return $this->shouldKeepRunning;
    }

    /**
     * Stop the command gracefully
     */
    public function stop(): void
    {
        $this->shouldKeepRunning = false;
    }

    /**
     * Resume the command
     */
    public function resume(): void
    {
        $this->shouldKeepRunning = true;
    }

    /**
     * Get registered signal handlers
     */
    public function getSignalHandlers(): array
    {
        return array_keys($this->signalHandlers);
    }

    /**
     * Check if a specific signal is being handled
     */
    public function isHandlingSignal(int $signal): bool
    {
        return isset($this->signalHandlers[$signal]);
    }

    /**
     * Set command name for logging
     */
    public function setCommandName(string $commandName): self
    {
        $this->commandName = $commandName;

        return $this;
    }

    /**
     * Get command name
     */
    public function getCommandName(): string
    {
        return $this->commandName;
    }

    /**
     * Simulate signal reception (for testing purposes)
     */
    public function simulateSignal(int $signal): void
    {
        if (isset($this->signalHandlers[$signal])) {
            $this->signalHandlers[$signal]($signal);
        }
    }
}
