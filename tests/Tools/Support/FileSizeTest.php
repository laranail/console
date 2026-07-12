<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\FileSize;

final class FileSizeTest extends TestCase
{
    public function test_scales_through_units_at_the_1024_boundary(): void
    {
        self::assertSame('1023 B', FileSize::format(1023));
        self::assertSame('1 KB', FileSize::format(1024));
        self::assertSame('1 MB', FileSize::format(1024 ** 2));
        self::assertSame('1 GB', FileSize::format(1024 ** 3));
        self::assertSame('1 TB', FileSize::format(1024 ** 4));
    }

    public function test_caps_at_the_largest_unit(): void
    {
        // beyond TB it stays in TB rather than inventing a unit
        self::assertStringEndsWith(' TB', FileSize::format(1024 ** 5));
    }

    public function test_zero_and_precision(): void
    {
        self::assertSame('0 B', FileSize::format(0));
        self::assertSame('1.5 KB', FileSize::format(1536));        // round(1.5, 2) -> 1.5
        self::assertSame('2 KB', FileSize::format(1536, 0));        // round(1.5, 0) -> 2
    }
}
