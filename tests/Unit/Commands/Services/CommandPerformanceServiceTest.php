<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Tests\Unit\Commands\Services;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\ConsoleTools\Commands\Services\CommandPerformanceService;

final class CommandPerformanceServiceTest extends TestCase
{
    public function test_execution_time_is_zero_before_timer_starts(): void
    {
        $perf = new CommandPerformanceService;

        self::assertSame(0.0, $perf->getExecutionTime());
        self::assertSame('0ms', $perf->getFormattedExecutionTime());
    }

    public function test_start_then_end_timer_records_positive_execution_time(): void
    {
        $perf = new CommandPerformanceService;

        $perf->startTimer();
        usleep(2000);
        $perf->endTimer();

        self::assertGreaterThan(0.0, $perf->getExecutionTime());
    }

    public function test_formatted_execution_time_uses_ms_below_one_second(): void
    {
        $perf = new CommandPerformanceService;

        $perf->startTimer();
        $perf->endTimer();

        self::assertStringEndsWith('ms', $perf->getFormattedExecutionTime());
    }

    public function test_format_bytes_human_readable(): void
    {
        $perf = new CommandPerformanceService;

        self::assertSame('0 B', $perf->formatBytes(0));
        self::assertSame('1 KB', $perf->formatBytes(1024));
        self::assertSame('1 MB', $perf->formatBytes(1024 * 1024));
        // Negative values are clamped to zero.
        self::assertSame('0 B', $perf->formatBytes(-50));
    }

    public function test_memory_usage_returns_expected_keys(): void
    {
        $perf = new CommandPerformanceService;
        $perf->startTimer();
        $perf->endTimer();

        $usage = $perf->getMemoryUsage();

        self::assertArrayHasKey('start_memory', $usage);
        self::assertArrayHasKey('current_memory', $usage);
        self::assertArrayHasKey('peak_memory', $usage);
        self::assertArrayHasKey('memory_limit', $usage);
    }

    public function test_performance_summary_includes_command_and_metadata(): void
    {
        $perf = new CommandPerformanceService;
        $perf->startTimer();
        $perf->endTimer();

        $summary = $perf->getPerformanceSummary('my:command', ['foo' => 'bar']);

        self::assertSame('my:command', $summary['command']);
        self::assertSame(['foo' => 'bar'], $summary['metadata']);
        self::assertArrayHasKey('execution_time', $summary);
        self::assertIsArray($summary['memory_usage']);
        self::assertArrayHasKey('timestamp', $summary);
    }

    public function test_reset_clears_metrics(): void
    {
        $perf = new CommandPerformanceService;
        $perf->startTimer();
        usleep(1000);
        $perf->endTimer();

        self::assertGreaterThan(0.0, $perf->getExecutionTime());

        $perf->reset();

        self::assertSame(0.0, $perf->getExecutionTime());
    }
}
