<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Widgets\StatusLine;

final class StatusLineTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_each_status_renders_glyph_and_message(): void
    {
        $caps = Capabilities::fake(colors: false, unicode: true);
        $status = new StatusLine($caps);

        foreach (['success', 'error', 'warning', 'info', 'pending'] as $method) {
            $line = $status->{$method}('hello');
            self::assertStringContainsString('hello', $line, "{$method} keeps the message");
        }
    }

    public function test_unknown_status_falls_back_gracefully(): void
    {
        $caps = Capabilities::fake(colors: false, unicode: true);

        // unknown status -> empty glyph + white colour, message preserved (no crash)
        self::assertStringContainsString('msg', new StatusLine($caps)->line('nope', 'msg'));
    }

    public function test_message_is_sanitized(): void
    {
        $caps = Capabilities::fake(colors: false, unicode: true);

        $line = new StatusLine($caps)->success("ok\x1b[2J");
        self::assertStringNotContainsString("\x1b[2J", $line);
    }
}
