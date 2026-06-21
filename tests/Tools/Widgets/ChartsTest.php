<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\BrailleCanvas;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Support\Style;
use Simtabi\Laranail\Console\Tools\Widgets\ColumnChart;
use Simtabi\Laranail\Console\Tools\Widgets\Heatmap;
use Simtabi\Laranail\Console\Tools\Widgets\Histogram;
use Simtabi\Laranail\Console\Tools\Widgets\LineChart;
use Simtabi\Laranail\Console\Tools\Widgets\ScatterPlot;
use Simtabi\Laranail\Console\Tools\Widgets\StackedBar;

final class ChartsTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_braille_canvas_sets_dots_and_renders(): void
    {
        Capabilities::fake(colors: false, unicode: true);

        $canvas = new BrailleCanvas(2, 1); // 2 cells × 1 cell = 4×4 pixels
        $canvas->set(0, 0); // top-left dot → U+2801 (⠁)

        $lines = $canvas->render();

        self::assertCount(1, $lines);
        self::assertStringContainsString("\u{2801}", $lines[0]);
    }

    public function test_braille_canvas_ascii_fallback(): void
    {
        Capabilities::fake(colors: false, unicode: false);

        $canvas = new BrailleCanvas(2, 1);
        $canvas->line(0, 0, 3, 3);
        $out = implode('', $canvas->render());

        self::assertStringContainsString('*', $out);
        self::assertStringNotContainsString("\u{2800}", $out); // no braille without unicode
    }

    public function test_braille_canvas_pen_colours_cells(): void
    {
        Capabilities::fake(colors: true, unicode: true);

        $canvas = new BrailleCanvas(2, 1);
        $canvas->set(0, 0, 1);

        $out = implode('', $canvas->render([1 => Style::make()->fg('#ff0000')]));
        self::assertStringContainsString("\033[", $out);
    }

    public function test_column_chart_height_and_labels(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 40);

        $lines = ColumnChart::make(['A' => 1, 'B' => 10])->height(5)->renderLines();

        self::assertCount(6, $lines); // 5 bar rows + label row
        self::assertStringContainsString('A', end($lines));
        self::assertStringContainsString('B', end($lines));
    }

    public function test_column_chart_empty_is_safe(): void
    {
        self::assertSame([''], ColumnChart::make([])->renderLines());
    }

    public function test_line_chart_fits_width_and_height(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 50);

        $lines = LineChart::make([1, 5, 2, 8, 3, 9, 4])->height(6)->width(50)->renderLines();

        self::assertCount(6, $lines);
        foreach ($lines as $line) {
            self::assertLessThanOrEqual(50, DisplayWidth::of($line));
        }
    }

    public function test_line_chart_multi_series_uses_distinct_colours(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 50);

        $out = implode("\n", LineChart::make(['a' => [1, 2, 3], 'b' => [3, 2, 1]])->height(4)->renderLines());
        self::assertStringContainsString("\033[", $out);
    }

    public function test_scatter_plot_renders(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 40);

        $lines = ScatterPlot::make([[0, 0], [1, 1], [2, 4], [3, 9]])->height(5)->renderLines();
        self::assertCount(5, $lines);
    }

    public function test_heatmap_colour_and_shade_fallback(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 40);
        $coloured = implode('', Heatmap::make([[0, 1], [2, 3]])->renderLines());
        self::assertStringContainsString("\033[", $coloured); // bg colour

        Capabilities::fake(colors: false, unicode: true, width: 40);
        $shaded = implode('', Heatmap::make([[0, 1], [2, 3]])->renderLines());
        self::assertStringNotContainsString("\033[", $shaded);
        self::assertMatchesRegularExpression('/[░▒▓█ ]/u', $shaded);
    }

    public function test_histogram_bins_values(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 50);

        // 3 explicit bins → 3 columns + a label row
        $lines = Histogram::make([1, 1, 2, 3, 5, 8, 8, 9])->bins(3)->height(4)->renderLines();
        self::assertCount(5, $lines);
    }

    public function test_histogram_empty_is_safe(): void
    {
        self::assertSame([''], Histogram::make([])->renderLines());
    }

    public function test_stacked_bar_renders_segments_and_legend(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 40);

        $lines = StackedBar::make(['done' => 8, 'wip' => 2])->width(40)->renderLines();

        self::assertSame(3, count($lines));               // bar + 2 legend rows
        self::assertStringContainsString("\033[", $lines[0]); // coloured bar
        self::assertLessThanOrEqual(40, DisplayWidth::of($lines[0]));
        self::assertStringContainsString('done', $lines[1]);
        self::assertStringContainsString('80%', $lines[1]);   // 8/10
    }

    public function test_stacked_bar_no_colour_uses_distinct_glyphs(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 30);

        $bar = StackedBar::make(['a' => 1, 'b' => 1])->width(30)->renderLines()[0];

        self::assertStringNotContainsString("\033[", $bar);
        // two segments → two distinct glyphs present
        self::assertStringContainsString('█', $bar);
        self::assertStringContainsString('▓', $bar);
    }

    public function test_stacked_bar_legend_can_be_hidden_and_empty_is_safe(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 20);

        self::assertCount(1, StackedBar::make(['a' => 1])->showLegend(false)->renderLines());
        self::assertSame([''], StackedBar::make([])->renderLines());
        self::assertSame([''], StackedBar::make(['a' => 0])->renderLines()); // zero total
    }
}
