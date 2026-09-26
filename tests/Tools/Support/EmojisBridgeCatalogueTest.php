<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Emoji;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Support\EmojisBridge;
use Simtabi\Laranail\Console\Tools\Tests\Support\Fixtures\FakeEmojiCatalogue;

/**
 * The installed path, against a fake catalogue so it runs in console's own CI. The real adapter,
 * Simtabi\Laranail\Emojis\Laravel\ConsoleEmojiCatalogue, is tested in the laranail/emojis suite,
 * where both packages are genuinely installed.
 */
final class EmojisBridgeCatalogueTest extends TestCase
{
    protected function setUp(): void
    {
        EmojisBridge::fake(new FakeEmojiCatalogue);
    }

    protected function tearDown(): void
    {
        EmojisBridge::reset();
    }

    public function test_names_outside_the_map_resolve_from_the_catalogue(): void
    {
        $emoji = Emoji::make();

        self::assertTrue($emoji->has('unicorn'));
        self::assertSame('🦄', (clone $emoji)->unicode()->get('unicorn'));
        self::assertSame(':unicorn:', (clone $emoji)->ascii()->get('unicorn'));
        self::assertSame('a 🦄 b', (clone $emoji)->unicode()->render('a :unicorn: b'));
        self::assertSame('a b', $emoji->strip('a :unicorn: b'));
        self::assertContains('unicorn', $emoji->all());
        self::assertSame(1, array_count_values($emoji->all())['rocket'], 'map and catalogue names are merged once');
    }

    public function test_the_map_wins_over_the_catalogue_for_its_own_names(): void
    {
        self::assertSame('❌', Emoji::make()->unicode()->get('cross'));
        self::assertSame('[X]', Emoji::make()->ascii()->get('cross'));
        self::assertSame('🛑', Emoji::make()->unicode()->get('stop'));
    }

    public function test_custom_still_wins_over_everything(): void
    {
        self::assertSame('🐴', Emoji::make()->unicode()->with(['unicorn' => '🐴'])->get('unicorn'));
    }

    public function test_unknown_names_are_still_left_intact(): void
    {
        self::assertSame('', Emoji::make()->get('not-a-real-shortcode'));
        self::assertSame('keep :not-a-real-shortcode:', Emoji::make()->render('keep :not-a-real-shortcode:'));
    }

    public function test_ascii_render_converts_literal_emoji_too(): void
    {
        self::assertSame('ship -> :unicorn: [X]', Emoji::make()->ascii()->render('ship 🚀 🦄 :x:'));
        self::assertSame('ship 🚀 🦄', Emoji::make()->unicode()->render('ship 🚀 :unicorn:'));
    }

    public function test_the_catalogue_recognises_emoji_newer_than_the_width_table(): void
    {
        self::assertSame(2, DisplayWidth::of("\u{1FAEA}"));
        self::assertSame(5, DisplayWidth::of("ab \u{1FAEA}"));
    }

    public function test_a_failing_catalogue_degrades_to_the_built_in_behaviour(): void
    {
        EmojisBridge::fake(new FakeEmojiCatalogue(broken: true));

        self::assertSame('❌', Emoji::make()->unicode()->get('cross'));
        self::assertSame('', Emoji::make()->get('unicorn'));
        self::assertSame('ship 🚀', Emoji::make()->ascii()->render('ship 🚀'));
        self::assertSame(1, DisplayWidth::of("\u{1FAEA}"));
    }

    public function test_discovery_finds_the_adapter_exactly_when_it_is_autoloadable(): void
    {
        EmojisBridge::reset();

        self::assertSame(class_exists(EmojisBridge::ADAPTER), EmojisBridge::available());
    }
}
