<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\ConsoleTools\Events\CommandEvents;
use Symfony\Component\Console\Input\ArrayInput;

final class CommandEventsTest extends TestCase
{
    public function test_starting_factory_populates_fields(): void
    {
        $command = new \stdClass;
        $input = new ArrayInput([]);

        $event = CommandEvents::starting($command, $input);

        self::assertSame('starting', $event->action);
        self::assertSame('command', $event->type);
        self::assertSame($command, $event->command);
        self::assertSame($input, $event->input);
        self::assertNull($event->exitCode);
        self::assertGreaterThan(0.0, $event->firedAt);
    }

    public function test_terminating_factory_captures_exit_code(): void
    {
        $event = CommandEvents::terminating(new \stdClass, new ArrayInput([]), 1, null, ['k' => 'v']);

        self::assertSame('terminating', $event->action);
        self::assertSame(1, $event->exitCode);
        self::assertSame(['k' => 'v'], $event->metadata);
    }
}
