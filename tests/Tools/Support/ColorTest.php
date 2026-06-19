<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Color;

final class ColorTest extends TestCase
{
    /** @var array<string, string|false> */
    private array $savedEnv = [];

    protected function setUp(): void
    {
        foreach (['FORCE_COLOR', 'COLORTERM', 'NO_COLOR', 'TERM'] as $var) {
            $this->savedEnv[$var] = getenv($var);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->savedEnv as $var => $value) {
            $value === false ? putenv($var) : putenv("{$var}={$value}");
        }
    }

    private function forceTruecolor(): void
    {
        putenv('NO_COLOR');
        putenv('FORCE_COLOR=1');
        putenv('COLORTERM=truecolor');
    }

    public function test_fg_emits_truecolor_sequence(): void
    {
        $this->forceTruecolor();

        self::assertSame("\033[38;2;255;136;0mhi\033[0m", Color::make()->fg('hi', '#ff8800'));
    }

    public function test_fg_returns_plain_when_colour_disabled(): void
    {
        putenv('FORCE_COLOR');
        putenv('COLORTERM');
        putenv('NO_COLOR=1');

        self::assertSame('hi', Color::make()->fg('hi', '#ff8800'));
    }

    public function test_invalid_hex_is_not_styled(): void
    {
        $this->forceTruecolor();

        self::assertSame('hi', Color::make()->fg('hi', 'nothex'));
    }

    public function test_gradient_colours_each_character_and_resets_once(): void
    {
        $this->forceTruecolor();

        $out = Color::make()->gradient('ab', ['#000000', '#ffffff']);

        self::assertStringContainsString('a', $out);
        self::assertStringContainsString('b', $out);
        self::assertSame(1, substr_count($out, "\033[0m"));
        self::assertStringStartsWith("\033[38;2;0;0;0m", $out);
    }

    public function test_gradient_needs_two_stops(): void
    {
        $this->forceTruecolor();

        self::assertSame('abc', Color::make()->gradient('abc', ['#ffffff']));
    }

    public function test_hex_helpers(): void
    {
        self::assertTrue(Color::isValidHex('#ff8800'));
        self::assertFalse(Color::isValidHex('#fff'));
        self::assertSame([255, 136, 0], Color::hexToRgb('#ff8800'));
    }
}
