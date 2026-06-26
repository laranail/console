<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Enums\ControlChars;
use Simtabi\Laranail\Console\Tools\Enums\Sgr;
use Simtabi\Laranail\Console\Tools\Support\Csi;

final class AnsiPrimitivesTest extends TestCase
{
    public function test_control_chars_map_to_their_bytes(): void
    {
        self::assertSame("\x07", ControlChars::Bel->char());
        self::assertSame("\e", ControlChars::Esc->char());
        self::assertSame("\n", ControlChars::Lf->char());
        self::assertSame(127, ControlChars::Del->value);
    }

    public function test_sgr_builds_sequences_and_granular_resets(): void
    {
        self::assertSame("\e[4m", Sgr::Underline->open());
        self::assertSame("\e[1;4m", Sgr::sequence(Sgr::Bold, Sgr::Underline));
        self::assertSame("\e[24m", Sgr::UnderlineOff->open(), 'turn off only underline');
        self::assertSame("\e[1mhi\e[0m", Sgr::wrap('hi', Sgr::Bold));
    }

    public function test_csi_builds_control_sequences(): void
    {
        self::assertSame("\e[3A", Csi::sequence('A', 3));
        self::assertSame("\e[2;5H", Csi::sequence('H', 2, 5));
        self::assertSame("\e[2J", Csi::sequence('J', 2));
    }
}
