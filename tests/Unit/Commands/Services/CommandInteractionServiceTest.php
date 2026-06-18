<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Tests\Unit\Commands\Services;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\ConsoleTools\Commands\Services\CommandInteractionService;

/**
 * Exercises the non-interactive branches, which short-circuit Laravel
 * Prompts and return deterministic defaults without touching a TTY.
 */
final class CommandInteractionServiceTest extends TestCase
{
    private function service(): CommandInteractionService
    {
        return (new CommandInteractionService)->setNonInteractive(true);
    }

    public function test_non_interactive_flag_is_fluent_and_reported(): void
    {
        $service = new CommandInteractionService;

        self::assertFalse($service->isNonInteractive());
        self::assertSame($service, $service->setNonInteractive(true));
        self::assertTrue($service->isNonInteractive());
    }

    public function test_ask_text_returns_default_when_non_interactive(): void
    {
        self::assertSame('preset', $this->service()->askText('Name?', '', 'preset'));
    }

    public function test_ask_password_returns_empty_string_when_non_interactive(): void
    {
        self::assertSame('', $this->service()->askPassword('Secret?'));
    }

    public function test_ask_confirm_returns_default_when_non_interactive(): void
    {
        self::assertTrue($this->service()->askConfirm('Proceed?', true));
        self::assertFalse($this->service()->askConfirm('Proceed?'));
    }

    public function test_ask_select_returns_option_at_default_index(): void
    {
        $service = $this->service();

        self::assertSame('b', $service->askSelect('Pick', ['a', 'b', 'c'], 1));
        // Out-of-range default index yields an empty string.
        self::assertSame('', $service->askSelect('Pick', ['a'], 9));
    }

    public function test_ask_multi_select_returns_default_array(): void
    {
        self::assertSame(['x', 'y'], $this->service()->askMultiSelect('Pick', ['x', 'y', 'z'], ['x', 'y']));
    }

    public function test_show_spinner_invokes_callback_directly_when_non_interactive(): void
    {
        self::assertSame('spun', $this->service()->showSpinner('Working', fn (): string => 'spun'));
    }

    public function test_show_loading_delegates_to_spinner(): void
    {
        self::assertSame(99, $this->service()->showLoading('Loading', fn (): int => 99));
    }

    public function test_ask_with_validation_returns_default_without_running_validator(): void
    {
        $called = false;
        $validator = function () use (&$called): bool {
            $called = true;

            return true;
        };

        $result = $this->service()->askWithValidation('Q?', $validator, 'def');

        self::assertSame('def', $result);
        self::assertFalse($called);
    }

    public function test_confirm_action_returns_default_when_non_interactive(): void
    {
        self::assertTrue($this->service()->confirmAction('Sure?', true));
    }
}
