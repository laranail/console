<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Commands\Services;

use Simtabi\Laranail\Console\Tools\Commands\Services\CommandSignalService;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;

final class CommandSignalServiceTest extends TestCase
{
    public function test_runs_by_default(): void
    {
        $service = new CommandSignalService('signal:cmd');

        self::assertTrue($service->shouldKeepRunning());
        self::assertSame('signal:cmd', $service->getCommandName());
        self::assertSame([], $service->getSignalHandlers());
    }

    public function test_setup_registers_requested_signals(): void
    {
        $service = new CommandSignalService;
        $service->setupSignalHandling([SIGTERM, SIGINT]);

        self::assertTrue($service->isHandlingSignal(SIGTERM));
        self::assertTrue($service->isHandlingSignal(SIGINT));
        self::assertContains(SIGTERM, $service->getSignalHandlers());
        self::assertFalse($service->isHandlingSignal(SIGHUP));
    }

    public function test_stop_and_resume_toggle_running_state(): void
    {
        $service = new CommandSignalService;

        $service->stop();
        self::assertFalse($service->shouldKeepRunning());

        $service->resume();
        self::assertTrue($service->shouldKeepRunning());
    }

    public function test_simulating_registered_signal_stops_running(): void
    {
        $service = new CommandSignalService('cmd');
        $service->setupSignalHandling([SIGTERM]);

        $service->simulateSignal(SIGTERM);

        self::assertFalse($service->shouldKeepRunning());
    }

    public function test_simulating_unregistered_signal_is_a_noop(): void
    {
        $service = new CommandSignalService('cmd');
        $service->setupSignalHandling([SIGTERM]);

        $service->simulateSignal(SIGHUP);

        self::assertTrue($service->shouldKeepRunning());
    }

    public function test_set_command_name_is_fluent(): void
    {
        $service = new CommandSignalService;

        self::assertSame($service, $service->setCommandName('renamed'));
        self::assertSame('renamed', $service->getCommandName());
    }
}
