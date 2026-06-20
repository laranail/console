<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\BorderStyle;
use Simtabi\Laranail\Console\Tools\Support\Color;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Support\Symbols;
use Simtabi\Laranail\Console\Tools\Support\TimeFormat;

final class SupportTest extends TestCase
{
    public function test_display_width_ignores_ansi_escapes(): void
    {
        self::assertSame(5, DisplayWidth::of("\033[31mhello\033[0m"));
        self::assertSame(5, DisplayWidth::of('hello'));
    }

    public function test_display_width_padding(): void
    {
        self::assertSame('hi   ', DisplayWidth::pad('hi', 5));
        self::assertSame('   hi', DisplayWidth::padLeft('hi', 5));
        self::assertSame(' hi  ', DisplayWidth::center('hi', 5));
    }

    public function test_time_format_three_tiers(): void
    {
        self::assertSame('45.2s', TimeFormat::duration(45.234));
        self::assertSame('30s', TimeFormat::duration(30.0));
        self::assertSame('2m 18s', TimeFormat::duration(138));
        self::assertSame('1h 8m', TimeFormat::duration(4080));
        self::assertSame('0s', TimeFormat::duration(-5));
    }

    public function test_color_hex_helpers(): void
    {
        self::assertTrue(Color::isValidHex('#ff8800'));
        self::assertTrue(Color::isValidHex('ff8800'));
        self::assertFalse(Color::isValidHex('#zzz'));
        self::assertSame([255, 136, 0], Color::hexToRgb('#ff8800'));
    }

    public function test_symbols_have_unicode_and_ascii_variants(): void
    {
        self::assertSame('✓', Symbols::fancy()->get('success'));
        self::assertSame('[OK]', Symbols::ascii()->get('success'));
    }

    public function test_border_style_yields_one_family(): void
    {
        $g = BorderStyle::Rounded->glyphs();
        self::assertSame('╭', $g['tl']);
        self::assertSame('╯', $g['br']);
        self::assertSame(BorderStyle::Ascii, BorderStyle::Double->fallback());
    }
}
