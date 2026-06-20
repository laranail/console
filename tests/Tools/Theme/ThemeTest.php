<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Theme;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Theme\Palette;
use Simtabi\Laranail\Console\Tools\Theme\Theme;

final class ThemeTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_palette_defaults_and_overrides(): void
    {
        $p = Palette::make(['primary' => '#000000']);

        self::assertSame('#000000', $p->get('primary'));
        self::assertSame('#16a34a', $p->get('success')); // default kept
        self::assertNull($p->get('nope'));
    }

    public function test_palette_with_is_immutable(): void
    {
        $p = Palette::make();
        $p2 = $p->with('primary', '#111111');

        self::assertNotSame($p, $p2);
        self::assertSame('#111111', $p2->get('primary'));
        self::assertSame('#7c3aed', $p->get('primary'));
    }

    public function test_theme_derives_element_styles_from_palette(): void
    {
        Capabilities::fake(colors: true);

        $theme = Theme::make(['primary' => '#ff0000']);
        $h1 = $theme->style('h1');

        self::assertSame('#ff0000', $h1->fg);
        self::assertTrue($h1->bold);

        // Applying the style colours the text.
        self::assertStringContainsString('Title', $h1->apply('Title'));
        self::assertStringEndsWith("\033[0m", $h1->apply('Title'));
    }

    public function test_theme_style_override_wins(): void
    {
        $custom = Theme::make()->style('paragraph')->fg('#abcdef');
        $theme = Theme::make()->withStyle('paragraph', $custom);

        self::assertSame('#abcdef', $theme->style('paragraph')->fg);
    }

    public function test_unknown_element_is_an_empty_style(): void
    {
        self::assertTrue(Theme::make()->style('does-not-exist')->isEmpty());
    }
}
