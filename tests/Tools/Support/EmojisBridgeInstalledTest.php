<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use Simtabi\Laranail\Emojis\Core\Emojis;
use Symfony\Component\Console\Helper\Helper;
use Simtabi\Laranail\Console\Tools\Support\Emoji;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Support\EmojisBridge;

/**
 * The installed path: console delegating to `laranail/emojis`. Skipped when the package is not
 * autoloadable — console only suggests it, so a plain `composer install` of console never has it.
 */
final class EmojisBridgeInstalledTest extends TestCase
{
    private const string FAMILY = "\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467}\u{200D}\u{1F466}";

    protected function setUp(): void
    {
        if (! class_exists(Emojis::class)) {
            self::markTestSkipped('laranail/emojis is not installed.');
        }

        parent::setUp();
        EmojisBridge::reset();
    }

    protected function tearDown(): void
    {
        EmojisBridge::reset();
        parent::tearDown();
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
        self::assertGreaterThan(1000, count($emoji->all()));
    }

    public function test_the_map_wins_over_the_catalogue_for_its_own_names(): void
    {
        // The catalogue's `cross` is the latin cross and its `stop` is the stop button.
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

    public function test_an_instance_bound_by_the_application_is_the_one_used(): void
    {
        $this->app->instance(Emojis::class, Emojis::create()->addShortcode('shipit', '🚀'));

        self::assertSame('🚀', Emoji::make()->unicode()->get('shipit'));
    }

    public function test_display_width_counts_an_emoji_sequence_as_two_columns(): void
    {
        self::assertGreaterThan(2, Helper::width(self::FAMILY), 'precondition: Symfony overcounts it');
        self::assertSame(2, DisplayWidth::of(self::FAMILY));
        self::assertSame(2, DisplayWidth::of('<info>' . self::FAMILY . '</info>'));
        self::assertSame(2, DisplayWidth::of("\u{2139}\u{FE0F}"));
        self::assertSame(6, DisplayWidth::of('ab ' . self::FAMILY . ' '));
        self::assertSame(self::FAMILY . '  ', DisplayWidth::pad(self::FAMILY, 4));
    }

    public function test_display_width_truncate_never_splits_a_sequence(): void
    {
        self::assertSame('a' . self::FAMILY, DisplayWidth::truncate('a' . self::FAMILY . 'bc', 3));
        self::assertSame('a', DisplayWidth::truncate('a' . self::FAMILY . 'bc', 2));
    }

    public function test_text_without_sequences_keeps_the_symfony_path(): void
    {
        self::assertNull(EmojisBridge::width('café 日本 🚀'));
        self::assertSame(Helper::width('café 日本 🚀'), DisplayWidth::of('café 日本 🚀'));
    }
}
