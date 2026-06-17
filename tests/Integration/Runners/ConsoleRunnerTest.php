<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Tests\Integration\Runners;

use Orchestra\Testbench\TestCase;
use Simtabi\Laranail\ConsoleTools\Runners\ConsoleRunner;

final class ConsoleRunnerTest extends TestCase
{
    public function test_runs_in_console_context(): void
    {
        // PHPUnit runs in the console, so the default console check passes.
        self::assertTrue(ConsoleRunner::make()->wouldRun());
    }

    public function test_failing_condition_skips(): void
    {
        self::assertFalse(ConsoleRunner::make()->when(false, 'never')->wouldRun());
    }

    public function test_runs_callback_and_returns_value_when_expected(): void
    {
        $result = ConsoleRunner::make()->expectReturn()->run(fn (): string => 'ok');

        self::assertSame('ok', $result);
    }

    public function test_skipped_returns_default(): void
    {
        $result = ConsoleRunner::make()
            ->when(false)
            ->expectReturn('fallback')
            ->run(fn (): string => 'never');

        self::assertSame('fallback', $result);
    }
}
