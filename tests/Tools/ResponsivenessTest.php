<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\KeyValue;
use Simtabi\Laranail\Console\Tools\Widgets\Panel;
use Simtabi\Laranail\Console\Tools\Widgets\PanelBlock;
use Simtabi\Laranail\Console\Tools\Widgets\Summary;
use Simtabi\Laranail\Console\Tools\Widgets\Table;
use Simtabi\Laranail\Console\Tools\Widgets\Tree;

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

    public function test_keyvalue_clamps_long_values(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 20);

        $out = KeyValue::make(['url' => str_repeat('x', 80)])->render();
        foreach (explode("\n", $out) as $line) {
            self::assertLessThanOrEqual(20, DisplayWidth::of($line));
        }
    }

    public function test_tree_clamps_deep_rows(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 16);

        $out = Tree::make('root')->child(str_repeat('deep ', 20))->render();
        foreach (explode("\n", $out) as $line) {
            self::assertLessThanOrEqual(16, DisplayWidth::of($line));
        }
    }

    public function test_summary_divider_clamps_to_terminal(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 40);

        $out = Summary::make(['total' => 1, 'success' => 1])->render();
        // The divider (default 60) is clamped to the 40-col terminal; no line over 40.
        foreach (explode("\n", $out) as $line) {
            self::assertLessThanOrEqual(40, DisplayWidth::of(rtrim($line)));
        }
        self::assertStringContainsString(str_repeat('─', 40), $out);
    }

    public function test_truncate_ansi_preserves_and_closes_styling(): void
    {
        $styled = "\033[1;31mhello world\033[0m"; // bold red, 11 visible chars
        $out = DisplayWidth::truncateAnsi($styled, 5);

        self::assertSame(5, DisplayWidth::of($out));      // 'hello'
        self::assertStringStartsWith("\033[1;31m", $out); // style kept
        self::assertStringEndsWith("\033[0m", $out);      // closed (no bleed)
    }

    public function test_summary_content_clamps_to_narrow_terminal(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 18);

        $out = Summary::make(['total' => 5, 'success' => 4, 'failed' => 1])->render();
        foreach (explode("\n", $out) as $line) {
            self::assertLessThanOrEqual(18, DisplayWidth::of($line));
        }
    }

    public function test_panel_horizontal_fits_terminal(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 24);

        $panel = Panel::make()->horizontal()->border()->dividers()
            ->add(PanelBlock::make(str_repeat('a', 40)))
            ->add(PanelBlock::make(str_repeat('b', 40)));

        foreach ($panel->renderLines() as $line) {
            self::assertLessThanOrEqual(24, DisplayWidth::of($line));
        }
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
