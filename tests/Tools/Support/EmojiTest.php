<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Emoji;

final class EmojiTest extends TestCase
{
    public function test_get_returns_unicode_or_ascii_per_mode(): void
    {
        self::assertSame('🚀', Emoji::make()->unicode()->get('rocket'));
        self::assertSame('->', Emoji::make()->ascii()->get('rocket'));
    }

    public function test_unknown_name_returns_default_or_empty(): void
    {
        self::assertSame('', Emoji::make()->get('does-not-exist'));
        self::assertSame('?', Emoji::make()->get('does-not-exist', '?'));
        self::assertFalse(Emoji::make()->has('does-not-exist'));
        self::assertTrue(Emoji::make()->has('rocket'));
    }

    public function test_render_interpolates_shortcodes_and_leaves_unknown_intact(): void
    {
        self::assertSame('Deploying 🚀 now', Emoji::make()->unicode()->render('Deploying :rocket: now'));
        self::assertSame('Deploying -> now', Emoji::make()->ascii()->render('Deploying :rocket: now'));
        self::assertSame('keep :unknown: here', Emoji::make()->render('keep :unknown: here'));
    }

    public function test_with_adds_and_overrides_custom_emoji(): void
    {
        $emoji = Emoji::make()->unicode()->with(['deploy' => ['🚀', '>>'], 'rocket' => ['🛸', '<>']]);

        self::assertSame('🚀', $emoji->get('deploy'));
        self::assertSame('>>', (clone $emoji)->ascii()->get('deploy'));
        self::assertSame('🛸', $emoji->get('rocket'), 'custom overrides built-in');

        $single = Emoji::make()->with(['boom' => '💥']);
        self::assertSame('💥', $single->unicode()->get('boom'));
        self::assertSame('💥', $single->ascii()->get('boom'));
    }

    public function test_strip_removes_known_shortcodes(): void
    {
        self::assertSame('Done', Emoji::make()->strip('Done :tada:'));
        self::assertSame('a :unknown: b', Emoji::make()->strip('a :unknown: b'));
    }

    public function test_all_lists_names(): void
    {
        $all = Emoji::make()->with(['deploy' => '🚀'])->all();

        self::assertContains('rocket', $all);
        self::assertContains('deploy', $all);
    }
}
