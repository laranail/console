<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support\Fonts;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Support\Fonts\BlockFont;
use Simtabi\Laranail\Console\Tools\Support\Fonts\BuiltinFonts;
use Simtabi\Laranail\Console\Tools\Support\Fonts\FontDefinition;

final class BuiltinFontsTest extends TestCase
{
    public function test_registry_exposes_block(): void
    {
        self::assertContains('block', BuiltinFonts::names());
        self::assertTrue(BuiltinFonts::has('block'));
        self::assertInstanceOf(FontDefinition::class, BuiltinFonts::get('block'));
    }

    public function test_unknown_font_returns_null(): void
    {
        self::assertFalse(BuiltinFonts::has('nope'));
        self::assertNull(BuiltinFonts::get('nope'));
    }

    public function test_block_definition_shape(): void
    {
        $def = BlockFont::definition();

        self::assertSame(5, $def->height);
        self::assertSame(1, $def->gap);
        self::assertArrayHasKey('A', $def->chars);

        // every glyph has exactly `height` rows, all of equal display width
        foreach ($def->chars as $char => $rows) {
            self::assertCount($def->height, $rows, "glyph '{$char}' row count");
            $width = DisplayWidth::of($rows[0]);
            foreach ($rows as $row) {
                self::assertSame($width, DisplayWidth::of($row), "glyph '{$char}' uneven rows");
            }
        }
    }
}
