<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Tests\Unit\Commands;

use Simtabi\Laranail\ConsoleTools\Commands\LaranailCommand;
use Simtabi\Laranail\ConsoleTools\Commands\Services\CommandServiceManager;
use Simtabi\Laranail\ConsoleTools\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * A successful command that records metadata during handle().
 */
final class LifecycleSuccessCommand extends LaranailCommand
{
    protected $signature = 'laranail-test:success';

    protected $description = 'Lifecycle success test command';

    public function handle(): int
    {
        $this->addMetadata('handled', true);

        return self::SUCCESS;
    }
}

final class LaranailCommandTest extends TestCase
{
    private function makeCommand(): LifecycleSuccessCommand
    {
        $command = new LifecycleSuccessCommand;
        $command->setLaravel($this->app);

        return $command;
    }

    public function test_service_manager_is_wired_with_command_name(): void
    {
        $command = $this->makeCommand();
        $services = $command->getServices();

        self::assertInstanceOf(CommandServiceManager::class, $services);
        self::assertSame('laranail-test:success', $services->getCommandName());
    }

    public function test_metadata_helpers_round_trip(): void
    {
        $command = $this->makeCommand();
        $command->addMetadata('a', 1)->addMetadata('b', 2);

        self::assertSame(1, $command->getMetadata('a'));
        self::assertSame('default', $command->getMetadata('missing', 'default'));
        self::assertSame(['a' => 1, 'b' => 2], $command->getAllMetadata());
    }

    public function test_signals_can_be_stopped_and_resumed(): void
    {
        $command = $this->makeCommand();

        self::assertTrue($command->shouldKeepRunning());

        $command->stop();
        self::assertFalse($command->shouldKeepRunning());
    }

    public function test_performance_accessors_delegate_to_service(): void
    {
        $command = $this->makeCommand();

        // Before any timing the formatted time is the zero baseline.
        self::assertSame(0.0, $command->getExecutionTime());
        self::assertSame('0ms', $command->getFormattedExecutionTime());

        $summary = $command->getPerformanceSummary();
        self::assertSame('laranail-test:success', $summary['command']);
    }

    public function test_event_toggles_are_fluent(): void
    {
        $command = $this->makeCommand();

        self::assertSame($command, $command->useNativeEvents(false));
        self::assertSame($command, $command->useCustomEvents(false));
    }

    public function test_configure_services_is_fluent(): void
    {
        $command = $this->makeCommand();

        self::assertSame($command, $command->configureServices(['native_events' => false]));
    }

    /**
     * run() reads interactivity from the passed $input (not the not-yet-set
     * $this->input), so the full service-backed lifecycle executes cleanly and
     * handle() runs.
     */
    public function test_run_executes_the_full_lifecycle(): void
    {
        $command = $this->makeCommand();

        $exit = $command->run(new ArrayInput([]), new BufferedOutput);

        self::assertSame(LifecycleSuccessCommand::SUCCESS, $exit);
        self::assertTrue($command->getMetadata('handled'));
    }

    public function test_handle_works_in_isolation(): void
    {
        // handle() itself works; only run() trips the ordering bug above.
        $command = $this->makeCommand();

        self::assertSame(LaranailCommand::SUCCESS, $command->handle());
        self::assertTrue($command->getMetadata('handled'));
    }
}
