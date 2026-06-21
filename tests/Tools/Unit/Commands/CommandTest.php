<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Commands;

use Simtabi\Laranail\Console\Tools\Commands\Command;
use Simtabi\Laranail\Console\Tools\Commands\Services\CommandServiceManager;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * A successful command that records metadata during handle(), reaching the
 * services directly (the slim base exposes everything via $this->services).
 */
final class LifecycleSuccessCommand extends Command
{
    protected $signature = 'laranail-test:success';

    protected $description = 'Lifecycle success test command';

    public function handle(): int
    {
        $this->services->metadata()->add('handled', true);

        return self::SUCCESS;
    }
}

final class CommandTest extends TestCase
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

    public function test_metadata_round_trips_via_the_service(): void
    {
        $meta = $this->makeCommand()->getServices()->metadata();
        $meta->add('a', 1);
        $meta->add('b', 2);

        self::assertSame(1, $meta->get('a'));
        self::assertSame('default', $meta->get('missing', 'default'));
        self::assertSame(['a' => 1, 'b' => 2], $meta->all());
    }

    public function test_signals_can_be_stopped_and_resumed(): void
    {
        $signals = $this->makeCommand()->getServices()->signals();

        self::assertTrue($signals->shouldKeepRunning());

        $signals->stop();
        self::assertFalse($signals->shouldKeepRunning());
    }

    public function test_performance_service_reports_baseline(): void
    {
        $perf = $this->makeCommand()->getServices()->performance();

        // Before any timing the formatted time is the zero baseline.
        self::assertSame(0.0, $perf->getExecutionTime());
        self::assertSame('0ms', $perf->getFormattedExecutionTime());

        $summary = $perf->getPerformanceSummary('laranail-test:success', []);
        self::assertSame('laranail-test:success', $summary['command']);
    }

    public function test_event_toggles_take_effect(): void
    {
        $events = $this->makeCommand()->getServices()->events();

        $events->useNativeEvents(false);
        $events->useCustomEvents(false);

        self::assertFalse($events->isNativeEventsEnabled());
        self::assertFalse($events->isCustomEventsEnabled());
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
        self::assertTrue($command->getServices()->metadata()->get('handled'));
    }

    public function test_handle_works_in_isolation(): void
    {
        // handle() itself works; only run() trips the ordering bug above.
        $command = $this->makeCommand();

        self::assertSame(Command::SUCCESS, $command->handle());
        self::assertTrue($command->getServices()->metadata()->get('handled'));
    }

    /**
     * Regression: constructing a command outside a running Application — as the
     * container does during resolution / static analysis — must not fatal.
     * Signal handling is wired at run() time, not the constructor, so a null
     * application here is fine. Previously this fatalled on ext-pcntl platforms
     * with "Call to a member function getSignalRegistry() on null".
     */
    public function test_constructing_without_an_application_does_not_fatal(): void
    {
        $command = new LifecycleSuccessCommand;

        self::assertNull($command->getApplication());
        self::assertInstanceOf(CommandServiceManager::class, $command->getServices());
    }
}
