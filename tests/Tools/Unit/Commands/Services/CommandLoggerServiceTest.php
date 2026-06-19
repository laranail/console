<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Commands\Services;

use Illuminate\Support\Facades\Log;
use Mockery;
use Simtabi\Laranail\Console\Tools\Commands\Services\CommandLoggerService;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;

final class CommandLoggerServiceTest extends TestCase
{
    public function test_get_context_includes_command_timestamp_and_extras(): void
    {
        $service = new CommandLoggerService('my:cmd')->addContext('foo', 'bar');

        $context = $service->getContext();

        self::assertSame('my:cmd', $context['command']);
        self::assertArrayHasKey('timestamp', $context);
        self::assertSame('bar', $context['foo']);
    }

    public function test_clear_context_keeps_command_and_timestamp(): void
    {
        $service = new CommandLoggerService('my:cmd')->addContext('foo', 'bar');

        $service->clearContext();
        $context = $service->getContext();

        self::assertArrayNotHasKey('foo', $context);
        self::assertSame('my:cmd', $context['command']);
    }

    public function test_log_completion_with_zero_exit_logs_info(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Command completed successfully', Mockery::on(
                fn (array $d): bool => $d['exit_code'] === 0 && $d['success'] === true
            ));

        new CommandLoggerService('cmd')->logCompletion(0);
    }

    public function test_log_completion_with_nonzero_exit_logs_warning(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Command completed with errors', Mockery::on(
                fn (array $d): bool => $d['exit_code'] === 2 && $d['success'] === false
            ));

        new CommandLoggerService('cmd')->logCompletion(2);
    }

    public function test_log_start_emits_info(): void
    {
        Log::shouldReceive('info')->once()->with('Command started', Mockery::type('array'));

        new CommandLoggerService('cmd')->logStart();
    }

    public function test_log_signal_includes_signal_number(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Command received termination signal', Mockery::on(
                fn (array $d): bool => $d['signal'] === 15
            ));

        new CommandLoggerService('cmd')->logSignal(15);
    }
}
