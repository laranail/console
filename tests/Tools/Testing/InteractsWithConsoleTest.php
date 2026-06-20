<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Testing\InteractsWithConsole;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Widgets\Box;

final class InteractsWithConsoleTest extends TestCase
{
    use InteractsWithConsole;

    public function test_forces_a_deterministic_capability_profile(): void
    {
        $this->withConsoleCapabilities(colors: false, unicode: false, width: 40, interactive: false);

        $caps = Capabilities::detect();
        self::assertFalse($caps->supportsUnicode());
        self::assertFalse($caps->supportsColor());
        self::assertSame(40, $caps->width());
        self::assertFalse($caps->isInteractive());

        // A widget built now resolves against the forced ASCII profile.
        $out = Box::make(['hi'])->render();
        self::assertStringNotContainsString('─', $out);
    }

    public function test_null_dimensions_fall_back_to_detection(): void
    {
        $this->withConsoleCapabilities(unicode: true);

        // unicode is forced; width is still detected (a positive integer).
        self::assertTrue(Capabilities::detect()->supportsUnicode());
        self::assertGreaterThan(0, Capabilities::detect()->width());
    }

    public function test_clear_fake_restores_detection(): void
    {
        Capabilities::fake(unicode: false);
        self::assertFalse(Capabilities::detect()->supportsUnicode());

        Capabilities::clearFake();
        self::assertIsBool(Capabilities::detect()->supportsUnicode());
    }
}
