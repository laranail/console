<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Widgets\Button;

final class ButtonTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_renders_plain_bracketed_label_without_colour(): void
    {
        $caps = Capabilities::fake(colors: false, unicode: true);

        self::assertSame(['[ Save ]'], new Button('Save', 'primary', $caps)->renderLines());
    }

    public function test_focused_state_still_degrades_to_plain_without_colour(): void
    {
        $caps = Capabilities::fake(colors: false, unicode: true);

        self::assertSame(['[ Go ]'], new Button('Go', 'success', $caps)->focused()->renderLines());
    }

    public function test_unknown_role_does_not_crash(): void
    {
        $caps = Capabilities::fake(colors: false, unicode: true);

        self::assertSame(['[ X ]'], new Button('X', 'no-such-role', $caps)->renderLines());
    }

    public function test_applies_ansi_when_colour_is_available(): void
    {
        $caps = Capabilities::fake(colors: true, unicode: true);

        $line = new Button('Save', 'primary', $caps)->renderLines()[0];
        self::assertStringContainsString("\033[", $line);   // styled
        self::assertStringStartsWith('[', $line);
        self::assertStringEndsWith(']', $line);
    }
}
