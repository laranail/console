<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\Table;

final class ResponsivenessTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_box_clamps_to_narrow_terminal(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 20);

        $long = str_repeat('x', 200);
        foreach (explode("\n", Box::make([$long])->render()) as $line) {
            self::assertLessThanOrEqual(20, DisplayWidth::of($line));
        }
    }

    public function test_box_explicit_width_wins_over_responsive(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 20);

        // An explicit width is honoured even if it exceeds the terminal.
        $out = Box::make(['hi'])->width(30)->render();
        self::assertSame(30, DisplayWidth::of(explode("\n", $out)[0]));
    }

    public function test_box_responsive_false_does_not_clamp(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 20);

        $out = Box::make([str_repeat('x', 50)])->responsive(false)->render();
        self::assertGreaterThan(20, DisplayWidth::of(explode("\n", $out)[0]));
    }

    public function test_table_wraps_to_fit_when_overflowing(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 30);

        $out = Table::make()
            ->headers(['name', 'description'])
            ->rows([['alpha', str_repeat('word ', 30)]])
            ->render();

        foreach (explode("\n", rtrim($out, "\n")) as $line) {
            self::assertLessThanOrEqual(30, DisplayWidth::of($line));
        }
    }
}
