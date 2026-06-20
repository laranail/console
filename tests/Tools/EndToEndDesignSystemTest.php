<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Document\Document;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Theme\Theme;
use Simtabi\Laranail\Console\Tools\Widgets\BarChart;

final class EndToEndDesignSystemTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    /**
     * A full themed document (every element family) must never exceed the forced
     * width, at several widths.
     */
    public function test_document_respects_width_at_several_sizes(): void
    {
        foreach ([30, 50, 80] as $width) {
            Capabilities::fake(colors: true, unicode: true, width: $width);

            $doc = Document::make()
                ->width($width)
                ->h1('Release')
                ->paragraph(str_repeat('lorem ipsum dolor ', 10))
                ->bulletList([str_repeat('a', 60), 'short'])
                ->blockQuote(str_repeat('quoted ', 12))
                ->codeBlock('echo ' . str_repeat('x', 60) . ';')
                ->add(BarChart::make(['api' => 9, 'web' => 3])->width($width))
                ->render();

            foreach (explode("\n", $doc) as $line) {
                self::assertLessThanOrEqual(
                    $width,
                    DisplayWidth::of($line),
                    "line exceeded width {$width}: " . $line,
                );
            }
        }
    }

    public function test_document_degrades_to_ascii_without_unicode_or_colour(): void
    {
        Capabilities::fake(colors: false, unicode: false, width: 60);

        $out = Document::make()
            ->h1('Title')
            ->paragraph('Body')
            ->bulletList(['one'])
            ->render();

        self::assertStringNotContainsString("\033[", $out); // no colour
        self::assertStringContainsString('- one', $out);     // ascii marker
        self::assertStringContainsString('Title', $out);
    }

    public function test_custom_theme_propagates_through_document(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 60);

        $plain = new Document(null, Theme::make())->h1('Hi')->render();
        $themed = new Document(null, Theme::make(['primary' => '#ff0000']))->h1('Hi')->render();

        // The custom theme applies an ANSI colour sequence (depth — truecolor/256/16
        // — depends on the terminal, so assert only that styling is applied and that
        // a different palette yields different bytes).
        self::assertStringContainsString("\033[", $themed);
        self::assertStringContainsString('Hi', $themed);
        self::assertNotSame($plain, $themed);
    }
}
