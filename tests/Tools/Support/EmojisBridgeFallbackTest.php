<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Emoji;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Support\EmojisBridge;

/**
 * The not-installed path: console on its built-in map. Forced with fake(null) so it runs the same
 * whether or not laranail/emojis happens to be autoloadable.
 */
final class EmojisBridgeFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        EmojisBridge::fake(null);
    }

    protected function tearDown(): void
    {
        EmojisBridge::reset();
    }

    public function test_the_bridge_reports_unavailable_and_answers_nothing(): void
    {
        self::assertFalse(EmojisBridge::available());
        self::assertNull(EmojisBridge::glyph('rocket', true));
        self::assertSame([], EmojisBridge::names());
        self::assertSame('ship 🚀', EmojisBridge::toShortcodes('ship 🚀'));
        self::assertFalse(EmojisBridge::isEmoji("\u{1FAEA}"));
    }

    public function test_emoji_resolves_the_built_in_map_only(): void
    {
        $emoji = Emoji::make();

        self::assertSame('🚀', (clone $emoji)->unicode()->get('rocket'));
        self::assertSame('->', (clone $emoji)->ascii()->get('rocket'));
        self::assertFalse($emoji->has('unicorn'));
        self::assertSame('', $emoji->get('unicorn'));
        self::assertSame('keep :unicorn: here', $emoji->unicode()->render('keep :unicorn: here'));
        self::assertLessThan(100, count($emoji->all()), 'only the built-in map, no catalogue');
    }

    public function test_ascii_render_leaves_literal_emoji_untouched(): void
    {
        self::assertSame('ship 🚀 ->', Emoji::make()->ascii()->render('ship 🚀 :rocket:'));
    }

    public function test_emoji_sequences_measure_two_columns_without_the_catalogue(): void
    {
        self::assertSame(2, DisplayWidth::of("\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467}\u{200D}\u{1F466}"));
        self::assertSame(2, DisplayWidth::of("\u{2139}\u{FE0F}"));
    }

    public function test_an_emoji_newer_than_the_width_table_keeps_the_symfony_measurement(): void
    {
        // The documented limit of the fallback: without a catalogue, nothing says U+1FAEA is an emoji.
        self::assertSame(1, DisplayWidth::of("\u{1FAEA}"));
    }
}
