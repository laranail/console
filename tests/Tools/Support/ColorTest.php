<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Exceptions\InvalidColorException;
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

    public function test_parse_strict_returns_hex_for_valid_input(): void
    {
        self::assertSame('#ffffff', Color::parseStrict('#FFFFFF'));
        self::assertSame('#ff0000', Color::parseStrict('red'));
        self::assertSame('#abcdef', Color::parseStrict('abcdef'));
    }

    public function test_parse_strict_throws_on_invalid_input(): void
    {
        $this->expectException(InvalidColorException::class);

        Color::parseStrict('definitely-not-a-colour');
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

    public function test_parse_accepts_hex_named_rgb_hsl_and_xterm(): void
    {
        self::assertSame('#ff8800', Color::parse('#FF8800'));
        self::assertSame('#ff0000', Color::parse('red'));
        self::assertSame('#0a141e', Color::parse('rgb(10, 20, 30)'));
        self::assertSame('#ff0000', Color::parse('hsl(0, 100%, 50%)'));
        self::assertSame('#ffffff', Color::parse('@15'));
        self::assertNull(Color::parse('definitely-not-a-colour'));
    }

    public function test_fg_accepts_named_colour(): void
    {
        $this->forceTruecolor();

        self::assertSame("\033[38;2;255;0;0mhi\033[0m", Color::make()->fg('hi', 'red'));
    }

    public function test_downgrades_to_256_when_truecolor_absent(): void
    {
        putenv('NO_COLOR');
        putenv('COLORTERM');
        putenv('FORCE_COLOR=1');
        putenv('TERM=xterm-256color');

        $out = Color::make()->fg('hi', '#ff8800');
        self::assertStringContainsString("\033[38;5;", $out);
        self::assertStringNotContainsString('38;2;', $out);
    }

    public function test_downgrades_to_ansi16_on_basic_terminal(): void
    {
        putenv('NO_COLOR');
        putenv('COLORTERM');
        putenv('FORCE_COLOR=1');
        putenv('TERM=xterm');

        $out = Color::make()->fg('hi', '#ff0000');
        self::assertMatchesRegularExpression('/\033\[3\dm/', $out);
        self::assertStringNotContainsString('38;2;', $out);
        self::assertStringNotContainsString('38;5;', $out);
    }

    public function test_bg_emits_background_sequence(): void
    {
        $this->forceTruecolor();

        self::assertSame("\033[48;2;0;0;255mhi\033[0m", Color::make()->bg('hi', 'blue'));
    }

    public function test_blend_midpoint(): void
    {
        self::assertSame('#808080', Color::blend('#000000', '#ffffff', 0.5));
    }

    public function test_adaptive_picks_by_background(): void
    {
        putenv('COLORFGBG=15;0'); // dark background
        self::assertSame('#ffffff', Color::adaptive('#000000', '#ffffff'));

        putenv('COLORFGBG=0;15'); // light background
        self::assertSame('#000000', Color::adaptive('#000000', '#ffffff'));

        putenv('COLORFGBG');
    }
}
