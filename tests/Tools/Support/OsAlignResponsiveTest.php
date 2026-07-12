<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Align;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Os;
use Simtabi\Laranail\Console\Tools\Support\ResponsiveWidth;

final class OsAlignResponsiveTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_os_family_matches_runtime(): void
    {
        $os = Os::make();

        self::assertContains($os->family(), ['windows', 'macos', 'linux', 'unknown']);
        self::assertSame(PHP_OS_FAMILY === 'Linux', $os->isLinux());
        self::assertSame(PHP_OS_FAMILY === 'Darwin', $os->isMacos());
        self::assertSame(PHP_OS_FAMILY === 'Windows', $os->isWindows());
    }

    public function test_os_ci_detection_reads_env(): void
    {
        $saved = getenv('CI');
        putenv('CI=1');
        self::assertTrue(Os::make()->isCi());

        $saved === false ? putenv('CI') : putenv("CI={$saved}");
    }

    public function test_align_pad_fills_to_width(): void
    {
        self::assertSame(['hi  '], Align::pad(['hi'], 4, Align::LEFT));
        self::assertSame(['  hi'], Align::pad(['hi'], 4, Align::RIGHT));
        self::assertSame([' hi '], Align::pad(['hi'], 4, Align::CENTER));
    }

    public function test_align_place_prefixes_margin_without_stretching(): void
    {
        self::assertSame(['hi'], Align::place(['hi'], 6, Align::LEFT));
        self::assertSame(['    hi'], Align::place(['hi'], 6, Align::RIGHT));
        self::assertSame(['  hi'], Align::place(['hi'], 6, Align::CENTER));
    }

    public function test_responsive_cap_honours_explicit_then_terminal_then_none(): void
    {
        Capabilities::fake(width: 40);

        self::assertSame(50, ResponsiveWidth::cap(50, true)); // explicit wins
        self::assertSame(40, ResponsiveWidth::cap(null, true)); // terminal width
        self::assertNull(ResponsiveWidth::cap(null, false)); // opted out → no cap
    }
}
