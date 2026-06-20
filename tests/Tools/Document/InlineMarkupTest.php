<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Document;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Document\InlineMarkup;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Typography\Paragraph;

final class InlineMarkupTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_emphasis_emits_ansi_when_coloured(): void
    {
        Capabilities::fake(colors: true, unicode: true, interactive: false);

        $out = InlineMarkup::make()->render('a **b** _c_ `d`');

        self::assertStringContainsString("\033[", $out);   // styled
        self::assertStringContainsString('b', $out);
        self::assertStringContainsString('c', $out);
        self::assertStringContainsString('d', $out);
        self::assertStringNotContainsString('**', $out);   // markers consumed
    }

    public function test_degrades_to_plain_without_colour(): void
    {
        Capabilities::fake(colors: false, unicode: true, interactive: false);

        $out = InlineMarkup::make()->render('a **bold** and `code`');

        self::assertSame('a bold and code', $out);          // markers stripped, no ANSI
    }

    public function test_link_falls_back_to_plain(): void
    {
        Capabilities::fake(colors: false, unicode: true, interactive: false);

        $out = InlineMarkup::make()->render('see [docs](https://x.com)');
        self::assertSame('see docs (https://x.com)', $out);
    }

    public function test_emoji_shortcode_resolved(): void
    {
        Capabilities::fake(colors: false, unicode: true, interactive: false);

        self::assertStringContainsString('🚀', InlineMarkup::make()->render('go :rocket:'));
    }

    public function test_rich_paragraph_wraps_without_colour_bleed(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 20, interactive: false);

        $styled = InlineMarkup::make()->render('the **quick brown fox** jumps over the lazy dog again');
        $lines = Paragraph::rich($styled)->width(20)->renderLines();

        foreach ($lines as $line) {
            self::assertLessThanOrEqual(20, DisplayWidth::of($line));
            // a line that opens a colour must also close it (no bleed across wraps)
            if (str_contains($line, "\033[") && ! str_ends_with($line, "\033[0m")) {
                self::fail('styled line not reset-terminated: ' . $line);
            }
        }
    }

    public function test_rich_paragraph_carries_style_across_a_wrap(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 12, interactive: false);

        // a bold span long enough to wrap across two lines
        $styled = InlineMarkup::make()->render('**alpha bravo charlie delta echo**');
        $lines = Paragraph::rich($styled)->width(12)->renderLines();

        self::assertGreaterThan(1, count($lines));
        // every line that has visible text re-opens the bold (SGR 1) and resets
        foreach ($lines as $line) {
            if (trim(preg_replace('/\033\[[0-9;]*m/', '', $line) ?? '') === '') {
                continue;
            }
            self::assertStringContainsString("\033[1m", $line, 'bold carried onto: ' . $line);
            self::assertStringEndsWith("\033[0m", $line);
        }
    }
}
