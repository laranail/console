<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\BorderStyle;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Widgets\Banner;

final class BannerTest extends TestCase
{
    /**
     * @return list<int>
     */
    private function widths(string $rendered): array
    {
        return array_map(DisplayWidth::of(...), explode("\n", $rendered));
    }

    public function test_plain_banner_is_backward_compatible(): void
    {
        $out = Banner::make('Hello')->width(40)->render();

        self::assertStringContainsString('Hello', $out);
        self::assertCount(1, array_unique($this->widths($out)), 'lines must be equal width');
    }

    public function test_font_renders_multiline_big_text(): void
    {
        $out = Banner::make('HI')->font('block')->width(40)->render();
        $lines = explode("\n", $out);

        // 5 big-text rows + top & bottom rule.
        self::assertCount(7, $lines);
        self::assertStringContainsString('█', $out);
        self::assertCount(1, array_unique($this->widths($out)));
    }

    public function test_missing_font_falls_back_to_plain_title(): void
    {
        $out = Banner::make('Deploy')->font('no-such-font')->width(40)->render();

        self::assertStringContainsString('Deploy', $out);
        self::assertCount(3, explode("\n", $out), 'rule + title + rule');
    }

    public function test_alignment_and_border_keep_equal_widths(): void
    {
        $left = Banner::make('hey')->width(30)->align('left')->render();
        self::assertCount(1, array_unique($this->widths($left)));

        $boxed = Banner::make('hey')->width(30)->border(BorderStyle::Double)->render();
        self::assertStringContainsString('hey', $boxed);
        self::assertCount(1, array_unique($this->widths($boxed)));
    }
}
