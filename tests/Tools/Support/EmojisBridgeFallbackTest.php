<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\Helper;
use Simtabi\Laranail\Console\Tools\Support\Emoji;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Support\EmojisBridge;

/**
 * The not-installed path: console on its built-in map and Symfony's width. Runs whether or not
 * `laranail/emojis` is autoloadable, by disabling the bridge.
 */
final class EmojisBridgeFallbackTest extends TestCase
{
    private const string FAMILY = "\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467}\u{200D}\u{1F466}";

    protected function setUp(): void
    {
        EmojisBridge::disable();
    }

    protected function tearDown(): void
    {
        EmojisBridge::reset();
    }

    public function test_the_bridge_reports_unavailable_and_answers_nothing(): void
    {
        self::assertFalse(EmojisBridge::available());
        self::assertNull(EmojisBridge::glyph('rocket', true));
        self::assertNull(EmojisBridge::width(self::FAMILY));
        self::assertNull(EmojisBridge::truncate(self::FAMILY, 2));
        self::assertSame([], EmojisBridge::names());
        self::assertSame('ship 🚀', EmojisBridge::toShortcodes('ship 🚀'));
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

    public function test_display_width_keeps_the_symfony_measurement(): void
    {
        self::assertSame(Helper::width(self::FAMILY), DisplayWidth::of(self::FAMILY));
        self::assertSame(5, DisplayWidth::of('hello'));
    }
}
