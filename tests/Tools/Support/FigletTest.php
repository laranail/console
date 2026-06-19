<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Support\Figlet;

final class FigletTest extends TestCase
{
    public function test_bundled_block_font_renders_equal_width_lines(): void
    {
        $figlet = Figlet::font('block');
        self::assertSame(5, $figlet->height());
        self::assertContains('block', Figlet::builtins());

        $lines = $figlet->render('HI');
        self::assertCount(5, $lines);

        $widths = array_map(DisplayWidth::of(...), $lines);
        self::assertCount(1, array_unique($widths), 'all rows must share one width');
    }

    public function test_lowercase_falls_back_to_uppercase_glyphs(): void
    {
        self::assertSame(Figlet::font('block')->render('AB'), Figlet::font('block')->render('ab'));
    }

    public function test_unknown_font_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Figlet::font('does-not-exist');
    }

    public function test_parses_a_minimal_flf_font(): void
    {
        $flf = "flf2a\$ 2 2 4 -1 0\n"
            . ".\$@\n"
            . ".\$@@\n"   // glyph for ' ' (ascii 32): two rows, hardblank '$'
            . "AA@\n"
            . "AA@@\n";   // glyph for '!' (ascii 33)
        $path = sys_get_temp_dir() . '/laranail_test_' . getmypid() . '.flf';
        file_put_contents($path, $flf);

        try {
            $figlet = Figlet::font($path);
            self::assertSame(2, $figlet->height());
            $lines = $figlet->render('!');
            self::assertSame(['AA', 'AA'], $lines);
            // The hardblank in the space glyph renders as a real space.
            self::assertSame(['. ', '. '], $figlet->render(' '));
        } finally {
            @unlink($path);
        }
    }
}
