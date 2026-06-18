<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Tests\Unit\Observers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\ConsoleTools\Observers\ConsoleCommandObserver;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class ConsoleCommandObserverTest extends TestCase
{
    private Dispatcher $events;

    protected function setUp(): void
    {
        parent::setUp();
        $this->events = new Dispatcher;
    }

    private function fireStarting(string $command): void
    {
        $this->events->dispatch(new CommandStarting($command, new ArrayInput([]), new BufferedOutput));
    }

    private function fireFinished(string $command, int $exitCode = 0): void
    {
        $this->events->dispatch(new CommandFinished($command, new ArrayInput([]), new BufferedOutput, $exitCode));
    }

    public function test_start_and_finish_callbacks_fire_for_matching_commands(): void
    {
        $started = $finished = null;

        ConsoleCommandObserver::for('*', $this->events)
            ->onStart(function (CommandStarting $e) use (&$started): void {
                $started = $e->command;
            })
            ->onFinish(function (CommandFinished $e) use (&$finished): void {
                $finished = $e->exitCode;
            });

        $this->fireStarting('migrate');
        $this->fireFinished('migrate', 0);

        self::assertSame('migrate', $started);
        self::assertSame(0, $finished);
    }

    public function test_literal_filter_only_matches_named_command(): void
    {
        $hits = 0;

        ConsoleCommandObserver::for('cache:clear', $this->events)
            ->onStart(function () use (&$hits): void {
                $hits++;
            });

        $this->fireStarting('cache:clear');
        $this->fireStarting('migrate');

        self::assertSame(1, $hits);
    }

    public function test_wildcard_filter_matches_namespace(): void
    {
        $hits = 0;

        ConsoleCommandObserver::for('cache:*', $this->events)
            ->onStart(function () use (&$hits): void {
                $hits++;
            });

        $this->fireStarting('cache:clear');
        $this->fireStarting('cache:forget');
        $this->fireStarting('queue:work');

        self::assertSame(2, $hits);
    }

    public function test_regex_filter_matches(): void
    {
        $hits = 0;

        ConsoleCommandObserver::for('/^db:/', $this->events)
            ->onStart(function () use (&$hits): void {
                $hits++;
            });

        $this->fireStarting('db:seed');
        $this->fireStarting('migrate');

        self::assertSame(1, $hits);
    }

    public function test_array_filter_matches_any_pattern(): void
    {
        $hits = 0;

        ConsoleCommandObserver::for(['migrate', 'queue:*'], $this->events)
            ->onStart(function () use (&$hits): void {
                $hits++;
            });

        $this->fireStarting('migrate');
        $this->fireStarting('queue:work');
        $this->fireStarting('cache:clear');

        self::assertSame(2, $hits);
    }

    public function test_callable_filter_receives_command_name(): void
    {
        $hits = 0;

        ConsoleCommandObserver::for(fn (string $name): bool => str_contains($name, 'special'), $this->events)
            ->onStart(function () use (&$hits): void {
                $hits++;
            });

        $this->fireStarting('my:special:task');
        $this->fireStarting('ordinary');

        self::assertSame(1, $hits);
    }

    public function test_predicate_can_veto_matching_command(): void
    {
        $hits = 0;

        ConsoleCommandObserver::for('*', $this->events)
            ->when(fn (CommandStarting $e): bool => $e->command === 'allowed')
            ->onStart(function () use (&$hits): void {
                $hits++;
            });

        $this->fireStarting('allowed');
        $this->fireStarting('blocked');

        self::assertSame(1, $hits);
    }

    public function test_invoke_supports_zero_one_and_two_parameters(): void
    {
        $zero = $one = $two = false;

        ConsoleCommandObserver::for('*', $this->events)
            ->onStart(function () use (&$zero): void {
                $zero = true;
            })
            ->onStart(function (CommandStarting $e) use (&$one): void {
                $one = true;
            })
            ->onStart(function (CommandStarting $e, ConsoleCommandObserver $self) use (&$two): void {
                $two = $self instanceof ConsoleCommandObserver;
            });

        $this->fireStarting('anything');

        self::assertTrue($zero);
        self::assertTrue($one);
        self::assertTrue($two);
    }

    public function test_fetch_output_reads_buffered_output(): void
    {
        $output = new BufferedOutput;
        $output->write('hello from command');

        $event = new CommandFinished('cmd', new ArrayInput([]), $output, 0);

        self::assertStringContainsString('hello from command', (string) ConsoleCommandObserver::fetchOutput($event));
    }
}
