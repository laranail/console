<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Commands\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Command Error Service
 *
 * Handles error logging, exception management, and error context
 * for console commands. Provides structured error handling.
 */
class CommandErrorService
{
    protected array $context = [];

    public function __construct(protected string $commandName = '') {}

    /**
     * Set command name for context
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
     * Log error with context
     */
    public function logError(Throwable $e, array $additionalContext = []): void
    {
        $logData = array_merge($this->context, $additionalContext, [
            'command' => $this->commandName,
            'exception' => $e::class,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'timestamp' => now()->toISOString(),
        ]);

        Log::error('Command Error', $logData);
    }

    /**
     * Execute callback with error handling
     */
    public function executeWithErrorHandling(callable $callback, string $operation = 'operation'): mixed
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            $this->logError($e, ['operation' => $operation]);
            throw $e;
        }
    }

    /**
     * Execute callback with fallback on error
     */
    public function executeWithFallback(callable $callback, mixed $fallback = null, string $operation = 'operation'): mixed
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            $this->logError($e, ['operation' => $operation, 'fallback_used' => true]);

            return $fallback;
        }
    }

    /**
     * Get current context
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Clear context
     */
    public function clearContext(): self
    {
        $this->context = [];

        return $this;
    }
}
