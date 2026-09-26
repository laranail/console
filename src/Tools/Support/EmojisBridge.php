<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

use Throwable;
use Simtabi\Laranail\Console\Tools\Contracts\EmojiCatalogue;

/**
 * Finds the optional emoji catalogue and shields console from it.
 *
 * Console is a foundation package, so `laranail/emojis` is suggested, never required — and since
 * emojis requires console, the dependency can only point one way. Console owns the
 * {@see EmojiCatalogue} contract; emojis ships {@see self::ADAPTER} implementing it, and this class
 * discovers that adapter by name. When it is absent, every method answers null (or the input
 * unchanged) and callers keep their built-in behaviour.
 *
 * A failure inside the catalogue is treated as "not installed" for that call: console output
 * degrades to its built-in behaviour rather than throwing.
 *
 * @internal
 */
final class EmojisBridge
{
    /**
     * The adapter `laranail/emojis` ships. Named as a string because console cannot import it; the
     * emojis suite asserts this constant against the real class, so a rename there fails there.
     */
    public const string ADAPTER = 'Simtabi\\Laranail\\Emojis\\Laravel\\ConsoleEmojiCatalogue';

    private static ?EmojiCatalogue $adapter = null;

    private static bool $discovered = false;

    private static ?EmojiCatalogue $fake = null;

    private static bool $faked = false;

    /**
     * The catalogue in use, or null when none is installed.
     */
    public static function catalogue(): ?EmojiCatalogue
    {
        if (self::$faked) {
            return self::$fake;
        }

        if (! self::$discovered) {
            self::$discovered = true;
            self::$adapter = self::discover();
        }

        return self::$adapter;
    }

    public static function available(): bool
    {
        return self::catalogue() instanceof EmojiCatalogue;
    }

    public static function glyph(string $name, bool $unicode): ?string
    {
        return self::attempt(static fn (EmojiCatalogue $catalogue): ?string => $catalogue->glyph($name, $unicode));
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return self::attempt(static fn (EmojiCatalogue $catalogue): array => $catalogue->names()) ?? [];
    }

    /**
     * Rewrite literal emoji as `:shortcode:` text. Returns the input unchanged when no catalogue is
     * installed or the text is pure ASCII.
     */
    public static function toShortcodes(string $text): string
    {
        if (preg_match('/[^\x00-\x7F]/', $text) !== 1) {
            return $text;
        }

        return self::attempt(static fn (EmojiCatalogue $catalogue): string => $catalogue->toShortcodes($text)) ?? $text;
    }

    public static function isEmoji(string $cluster): bool
    {
        return self::attempt(static fn (EmojiCatalogue $catalogue): bool => $catalogue->isEmoji($cluster)) ?? false;
    }

    /**
     * Use this catalogue instead of discovering one; null behaves as though none were installed.
     * For tests — mirrors {@see Capabilities::fake()}.
     */
    public static function fake(?EmojiCatalogue $catalogue): void
    {
        self::$faked = true;
        self::$fake = $catalogue;
    }

    public static function reset(): void
    {
        self::$faked = false;
        self::$fake = null;
        self::$discovered = false;
        self::$adapter = null;
    }

    private static function discover(): ?EmojiCatalogue
    {
        $class = self::ADAPTER;

        if (! class_exists($class) || ! is_a($class, EmojiCatalogue::class, true)) {
            return null;
        }

        try {
            return new $class;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @template T
     *
     * @param callable(EmojiCatalogue): T $call
     *
     * @return T|null
     */
    private static function attempt(callable $call): mixed
    {
        $catalogue = self::catalogue();

        if (! $catalogue instanceof EmojiCatalogue) {
            return null;
        }

        try {
            return $call($catalogue);
        } catch (Throwable) {
            return null;
        }
    }
}
