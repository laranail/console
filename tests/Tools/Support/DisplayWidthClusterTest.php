<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Support\EmojisBridge;

/**
 * Width by grapheme cluster, with no catalogue installed: an emoji sequence is two columns and is
 * never cut apart, and text without one measures exactly as Symfony does.
 */
final class DisplayWidthClusterTest extends TestCase
{
    private const string FAMILY = "\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467}\u{200D}\u{1F466}";

    protected function setUp(): void
    {
        EmojisBridge::fake(null);
    }

    protected function tearDown(): void
    {
        EmojisBridge::reset();
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function sequences(): iterable
    {
        yield 'ZWJ family' => [self::FAMILY, 2];
        yield 'VS16' => ["\u{2139}\u{FE0F}", 2];
        yield 'keycap' => ["1\u{FE0F}\u{20E3}", 2];
        yield 'skin tone' => ["\u{1F44D}\u{1F3FD}", 2];
        yield 'two flags' => ["\u{1F1F0}\u{1F1EA}\u{1F1EC}\u{1F1E7}", 4];
        yield 'subdivision flag' => ["\u{1F3F4}\u{E0067}\u{E0062}\u{E0073}\u{E0063}\u{E0074}\u{E007F}", 2];
        yield 'in text' => ['ab ' . self::FAMILY . ' c', 7];
        yield 'styled' => ['<info>' . self::FAMILY . '</info>', 2];
    }

    #[DataProvider('sequences')]
    public function test_an_emoji_sequence_is_two_columns(string $text, int $width): void
    {
        self::assertSame($width, DisplayWidth::of($text));
    }

    public function test_text_without_sequences_measures_as_symfony_does(): void
    {
        foreach (['hello', 'café 日本', '🚀 ✅', "\u{1F600}x", '→ • ✓'] as $text) {
            self::assertSame(Helper::width($text), DisplayWidth::of($text), $text);
        }
    }

    public function test_padding_uses_the_cluster_width(): void
    {
        self::assertSame(self::FAMILY . '  ', DisplayWidth::pad(self::FAMILY, 4));
        self::assertSame(' ' . self::FAMILY . ' ', DisplayWidth::center(self::FAMILY, 4));
    }

    public function test_truncate_never_splits_a_sequence(): void
    {
        self::assertSame('a' . self::FAMILY, DisplayWidth::truncate('a' . self::FAMILY . 'bc', 3));
        self::assertSame('a', DisplayWidth::truncate('a' . self::FAMILY . 'bc', 2));
        self::assertSame('日本', DisplayWidth::truncate('日本語', 5));
    }

    public function test_truncate_ansi_never_splits_a_sequence_and_closes_styles(): void
    {
        $styled = "\e[31ma" . self::FAMILY . "bc\e[0m";

        self::assertSame("\e[31ma" . self::FAMILY . "\e[0m", DisplayWidth::truncateAnsi($styled, 3));
        self::assertSame("\e[31ma\e[0m", DisplayWidth::truncateAnsi($styled, 2));
    }

    public function test_invalid_utf8_does_not_throw(): void
    {
        self::assertIsInt(DisplayWidth::of("\xF0\x9F" . self::FAMILY));
        self::assertIsString(DisplayWidth::truncate("\xF0\x9F" . self::FAMILY . 'abc', 2));
    }
}
