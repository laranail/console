<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Tests\Unit\Commands\Services;

use Illuminate\Support\Facades\Log;
use Mockery;
use RuntimeException;
use Simtabi\Laranail\ConsoleTools\Commands\Services\CommandErrorService;
use Simtabi\Laranail\ConsoleTools\Tests\TestCase;

final class CommandErrorServiceTest extends TestCase
{
    public function test_context_is_accumulated_and_cleared(): void
    {
        $service = new CommandErrorService('cmd');

        $service->addContext('a', 1)->addContextMany(['b' => 2, 'c' => 3]);
        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3], $service->getContext());

        $service->clearContext();
        self::assertSame([], $service->getContext());
    }

    public function test_log_error_writes_structured_context(): void
    {
        $captured = [];
        Log::shouldReceive('error')
            ->once()
            ->with('Command Error', Mockery::on(function (array $data) use (&$captured): bool {
                $captured = $data;

                return true;
            }));

        (new CommandErrorService('my:cmd'))
            ->addContext('user', 42)
            ->logError(new RuntimeException('boom'), ['operation' => 'sync']);

        self::assertSame('my:cmd', $captured['command']);
        self::assertSame(RuntimeException::class, $captured['exception']);
        self::assertSame('boom', $captured['message']);
        self::assertSame(42, $captured['user']);
        self::assertSame('sync', $captured['operation']);
    }

    public function test_execute_with_error_handling_returns_value_on_success(): void
    {
        Log::shouldReceive('error')->never();

        $result = (new CommandErrorService)->executeWithErrorHandling(fn (): string => 'ok');

        self::assertSame('ok', $result);
    }

    public function test_execute_with_error_handling_logs_and_rethrows(): void
    {
        Log::shouldReceive('error')->once();

        $this->expectException(RuntimeException::class);

        (new CommandErrorService)->executeWithErrorHandling(function (): never {
            throw new RuntimeException('fail');
        });
    }

    public function test_execute_with_fallback_returns_fallback_on_error(): void
    {
        Log::shouldReceive('error')->once();

        $result = (new CommandErrorService)->executeWithFallback(
            function (): never {
                throw new RuntimeException('fail');
            },
            'fallback'
        );

        self::assertSame('fallback', $result);
    }

    public function test_execute_with_fallback_returns_value_on_success(): void
    {
        Log::shouldReceive('error')->never();

        $result = (new CommandErrorService)->executeWithFallback(fn (): int => 7, 'fallback');

        self::assertSame(7, $result);
    }
}
