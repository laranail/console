<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Commands\Services;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

/**
 * Command Interaction Service
 *
 * Handles user interactions, prompts, and input validation
 * for console commands. Provides Laravel Prompts integration.
 */
class CommandInteractionService
{
    protected bool $nonInteractive = false;

    /**
     * Set non-interactive mode
     */
    public function setNonInteractive(bool $nonInteractive): self
    {
        $this->nonInteractive = $nonInteractive;

        return $this;
    }

    /**
     * Check if in non-interactive mode
     */
    public function isNonInteractive(): bool
    {
        return $this->nonInteractive;
    }

    /**
     * Ask for text input with Laravel Prompts
     */
    public function askText(string $label, string $placeholder = '', string $default = '', bool $required = false): string
    {
        if ($this->nonInteractive) {
            return $default;
        }

        return text(
            label: $label,
            placeholder: $placeholder,
            default: $default,
            required: $required
        );
    }

    /**
     * Ask for password input with Laravel Prompts
     */
    public function askPassword(string $label, string $placeholder = ''): string
    {
        if ($this->nonInteractive) {
            return '';
        }

        return password(
            label: $label,
            placeholder: $placeholder
        );
    }

    /**
     * Ask for confirmation with Laravel Prompts
     */
    public function askConfirm(string $label, bool $default = false): bool
    {
        if ($this->nonInteractive) {
            return $default;
        }

        return confirm(
            label: $label,
            default: $default
        );
    }

    /**
     * Ask for selection with Laravel Prompts
     */
    public function askSelect(string $label, array $options, int $default = 0): string
    {
        if ($this->nonInteractive) {
            return $options[$default] ?? '';
        }

        return (string) select(
            label: $label,
            options: $options,
            default: $default
        );
    }

    /**
     * Ask for multiple selections with Laravel Prompts
     */
    public function askMultiSelect(string $label, array $options, array $default = []): array
    {
        if ($this->nonInteractive) {
            return $default;
        }

        return multiselect(
            label: $label,
            options: $options,
            default: $default
        );
    }

    /**
     * Show spinner for operations with unknown duration
     */
    public function showSpinner(string $message, callable $callback): mixed
    {
        if ($this->nonInteractive) {
            return $callback();
        }

        return spin(static fn () => $callback(), $message);
    }

    /**
     * Ask for user input with validation
     */
    public function askWithValidation(string $question, ?callable $validator = null, mixed $default = null): mixed
    {
        if ($this->nonInteractive) {
            return $default;
        }

        do {
            $answer = $this->askText($question, default: (string) $default);

            if ($validator === null || $validator($answer)) {
                return $answer;
            }

            error('Invalid input. Please try again.');
        } while (true);
    }

    /**
     * Confirm an action with the user
     */
    public function confirmAction(string $question, bool $default = false): bool
    {
        if ($this->nonInteractive) {
            return $default;
        }

        return $this->askConfirm($question, $default);
    }

    /**
     * Show loading message with spinner
     */
    public function showLoading(string $message, callable $callback): mixed
    {
        return $this->showSpinner($message, $callback);
    }
}
