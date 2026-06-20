<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\BorderStyle;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Widgets\Banner;
use Simtabi\Laranail\Console\Tools\Widgets\BannerTheme;
use Simtabi\Laranail\Console\Tools\Widgets\BarChart;

final class BarChartBannerThemeTest extends TestCase
{
    protected function setUp(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 40, interactive: false);
    }

    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_bar_chart_scales_and_fits_width(): void
    {
        $out = BarChart::make(['api' => 10, 'web' => 5, 'cli' => 0])->width(30)->renderLines();

        self::assertCount(3, $out);
        foreach ($out as $line) {
            self::assertLessThanOrEqual(30, DisplayWidth::of($line));
        }
        // Largest value gets the longest fill; zero gets none.
        self::assertGreaterThan(substr_count($out[1], '█'), substr_count($out[0], '█'));
        self::assertStringNotContainsString('█', $out[2]);
    }

    public function test_bar_chart_ascii_fallback(): void
    {
        Capabilities::fake(colors: false, unicode: false, width: 40, interactive: false);

        $out = BarChart::make(['x' => 10])->width(20)->renderLines();
        self::assertStringContainsString('#', $out[0]);
        self::assertStringNotContainsString('█', $out[0]);
    }

    public function test_banner_theme_applies_border_and_align(): void
    {
        $theme = BannerTheme::make('error');

        self::assertSame(BorderStyle::Heavy, $theme->border);
        self::assertSame('center', $theme->align);

        // The fluent shortcut renders without error and frames the title.
        $out = Banner::error('Boom')->render();
        self::assertStringContainsString('Boom', $out);
    }

    public function test_banner_theme_explicit_setter_overrides(): void
    {
        // theme() sets defaults; a later setter wins.
        $out = Banner::make('Hi')->theme('success')->align('left')->render();
        self::assertStringContainsString('Hi', $out);
    }
}
