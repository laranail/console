<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Runners;

use RuntimeException;
use Simtabi\Laranail\Console\Tools\Runners\BaseRunner;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Throwable;

/**
 * Concrete runner used to exercise the abstract BaseRunner behaviour.
 */
final class TestRunner extends BaseRunner
{
    protected function initialize(): void
    {
        // No default conditions.
    }
}

final class BaseRunnerTest extends TestCase
{
    public function test_runs_callback_and_returns_value_when_expected(): void
    {
        $result = TestRunner::make()->expectReturn()->run(fn (): string => 'value');

        self::assertSame('value', $result);
    }

    public function test_returns_null_when_expect_return_is_off(): void
    {
        $ran = false;

        $result = TestRunner::make()->run(function () use (&$ran): string {
            $ran = true;

            return 'ignored';
        });

        self::assertNull($result);
        self::assertTrue($ran);
    }

    public function test_failing_when_condition_skips_and_returns_default(): void
    {
        $ran = false;

        $result = TestRunner::make()
            ->when(false, 'never')
            ->expectReturn('fallback')
            ->run(function () use (&$ran): string {
                $ran = true;

                return 'value';
            });

        self::assertFalse($ran);
        self::assertSame('fallback', $result);
        self::assertFalse(TestRunner::make()->when(false)->wouldRun());
    }

    public function test_unless_inverts_the_condition(): void
    {
        self::assertTrue(TestRunner::make()->unless(false)->wouldRun());
        self::assertFalse(TestRunner::make()->unless(true)->wouldRun());
    }

    public function test_conditional_helpers_short_circuit_chain(): void
    {
        // whenTruthy(false) sets shouldRun false; later when() must not flip it back.
        $runner = TestRunner::make()->whenTruthy(0)->when(true);

        self::assertFalse($runner->wouldRun());
    }

    public function test_when_not_empty_evaluates_collection(): void
    {
        self::assertTrue(TestRunner::make()->whenNotEmpty([1])->wouldRun());
        self::assertFalse(TestRunner::make()->whenNotEmpty([])->wouldRun());
    }

    public function test_lifecycle_callbacks_run_in_order_on_success(): void
    {
        $order = [];

        $result = TestRunner::make()
            ->expectReturn()
            ->before(function () use (&$order): void {
                $order[] = 'before';
            })
            ->after(function ($result) use (&$order): void {
                $order[] = 'after';
            })
            ->onSuccess(function () use (&$order): void {
                $order[] = 'success';
            })
            ->finally(function () use (&$order): void {
                $order[] = 'finally';
            })
            ->run(function () use (&$order): string {
                $order[] = 'run';

                return 'ok';
            });

        self::assertSame('ok', $result);
        self::assertSame(['before', 'run', 'after', 'success', 'finally'], $order);
    }

    public function test_after_callback_can_override_result_when_expecting_return(): void
    {
        $result = TestRunner::make()
            ->expectReturn()
            ->after(fn ($value): string => $value . '-mutated')
            ->run(fn (): string => 'base');

        self::assertSame('base-mutated', $result);
    }

    public function test_skipped_callback_fires_when_conditions_fail(): void
    {
        $skipped = false;

        TestRunner::make()
            ->when(false)
            ->whenSkipped(function () use (&$skipped): void {
                $skipped = true;
            })
            ->run(fn (): null => null);

        self::assertTrue($skipped);
    }

    public function test_error_callback_can_supply_fallback_value(): void
    {
        $handled = null;

        $result = TestRunner::make()
            ->expectReturn()
            ->onError(function (Throwable $e) use (&$handled): string {
                $handled = $e->getMessage();

                return 'recovered';
            })
            ->run(function (): never {
                throw new RuntimeException('boom');
            });

        self::assertSame('boom', $handled);
        self::assertSame('recovered', $result);
    }

    public function test_error_without_fallback_returns_default_and_runs_finally(): void
    {
        $finallyHadError = null;

        $result = TestRunner::make()
            ->expectReturn('default')
            ->finally(function ($res, $error) use (&$finallyHadError): void {
                $finallyHadError = $error instanceof Throwable;
            })
            ->run(function (): never {
                throw new RuntimeException('boom');
            });

        self::assertSame('default', $result);
        self::assertTrue($finallyHadError);
    }

    public function test_run_or_invokes_alternative_when_skipped(): void
    {
        $result = TestRunner::make()
            ->when(false)
            ->runOr(fn (): string => 'primary', fn (): string => 'alternative');

        self::assertSame('alternative', $result);
    }

    public function test_run_or_invokes_primary_when_runnable(): void
    {
        $result = TestRunner::make()
            ->expectReturn()
            ->runOr(fn (): string => 'primary', fn (): string => 'alternative');

        self::assertSame('primary', $result);
    }

    public function test_with_context_is_passed_to_callbacks(): void
    {
        $seen = null;

        TestRunner::make()
            ->withContext(['tenant' => 'acme'])
            ->before(function (array $context) use (&$seen): void {
                $seen = $context;
            })
            ->run(fn (): null => null);

        self::assertSame(['tenant' => 'acme'], $seen);
    }

    public function test_debug_reports_state_and_registered_callbacks(): void
    {
        $runner = TestRunner::make()
            ->when(true, 'check')
            ->before(fn (): null => null)
            ->onError(fn (): null => null);

        $debug = $runner->debug();

        self::assertTrue($debug['should_run']);
        self::assertCount(1, $debug['conditions']);
        self::assertTrue($debug['callbacks']['before']);
        self::assertTrue($debug['callbacks']['error']);
        self::assertFalse($debug['callbacks']['after']);
    }

    public function test_passed_conditions_filters_records(): void
    {
        $runner = TestRunner::make()->when(true, 'a');

        self::assertCount(1, $runner->getPassedConditions());
    }

    public function test_custom_logger_receives_error_logs(): void
    {
        $logs = [];

        TestRunner::make()
            ->withLogger(function (string $level, string $message, array $context) use (&$logs): void {
                $logs[] = $level;
            })
            ->run(function (): never {
                throw new RuntimeException('boom');
            });

        // Errors are always logged regardless of logExecution flag.
        self::assertContains('error', $logs);
    }
}
