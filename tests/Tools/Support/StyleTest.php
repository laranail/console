<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Style;

final class StyleTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_setters_are_immutable(): void
    {
        $base = Style::make();
        $bold = $base->bold();

        self::assertNotSame($base, $bold);
        self::assertTrue($bold->bold);
        self::assertFalse($base->bold);
    }

    public function test_empty_style_is_a_noop(): void
    {
        Capabilities::fake(colors: true);

        self::assertTrue(Style::make()->isEmpty());
        self::assertSame('hello', Style::make()->apply('hello'));
    }

    public function test_apply_wraps_with_attributes_and_resets_once(): void
    {
        Capabilities::fake(colors: true);

        $out = Style::make()->fg('red')->bold()->underline()->apply('hi');

        self::assertStringContainsString('hi', $out);
        self::assertStringEndsWith("\033[0m", $out);
        self::assertSame(1, substr_count($out, "\033[0m"));
        self::assertStringContainsString('1', $out); // bold code present
    }

    public function test_no_colour_strips_styling(): void
    {
        Capabilities::fake(colors: false);

        self::assertSame('hi', Style::make()->fg('red')->bold()->apply('hi'));
    }

    public function test_empty_text_is_untouched(): void
    {
        Capabilities::fake(colors: true);

        self::assertSame('', Style::make()->bold()->apply(''));
    }
}
