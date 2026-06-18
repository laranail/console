<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Notifications;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Notifications\ConsoleChannel;
use Simtabi\Laranail\Console\Tools\Notifications\Contracts\ConsoleChannelInterface;
use Symfony\Component\Console\Output\BufferedOutput;

final class ConsoleChannelTest extends TestCase
{
    public function test_name_and_contract(): void
    {
        $channel = new ConsoleChannel;

        self::assertInstanceOf(ConsoleChannelInterface::class, $channel);
        self::assertSame('console', $channel->getName());
        self::assertTrue($channel->isEnabled());
    }

    public function test_send_writes_message_to_symfony_output(): void
    {
        $output = new BufferedOutput;
        $channel = new ConsoleChannel([], $output);

        // Style tags (<comment>…</comment>) are consumed by Symfony's
        // formatter; assert the rendered message text is present.
        self::assertTrue($channel->send('Hello', ['level' => 'comment']));
        self::assertStringContainsString('Hello', $output->fetch());
    }

    public function test_send_includes_data_when_enabled_and_present(): void
    {
        $output = new BufferedOutput;
        (new ConsoleChannel([], $output))->send('Msg', ['foo' => 'bar']);

        self::assertStringContainsString('Data:', $output->fetch());
    }

    public function test_data_suppressed_when_show_data_false(): void
    {
        $output = new BufferedOutput;
        (new ConsoleChannel(['show_data' => false], $output))->send('Msg', ['foo' => 'bar']);

        self::assertStringNotContainsString('Data:', $output->fetch());
    }
}
