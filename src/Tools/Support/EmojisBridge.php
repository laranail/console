<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

use Throwable;
use Simtabi\Laranail\Emojis\Core\Emojis;
use Simtabi\Laranail\Emojis\Core\Enums\Mode;

/**
 * The one place console talks to the optional `laranail/emojis` package.
 *
 * Console is a foundation package, so `laranail/emojis` is suggested, never required. When it is
 * installed, {@see Emoji} resolves names outside its own map from the full Unicode catalogue and
 * {@see DisplayWidth} measures emoji sequences with it. When it is not, every method here answers
 * null (or the input unchanged) and callers fall back to their own logic.
 *
 * A failure inside the catalogue — a missing dataset, input it rejects — is treated as "not
 * installed" for that call: console output degrades to its built-in behaviour rather than throwing.
 *
 * @internal
 */
final class EmojisBridge
{
    /**
     * Sequences Symfony's width miscounts: ZWJ, VS16, keycap, regional indicators, skin-tone
     * modifiers and tag characters. Anything else is measured correctly already, so the (much
     * slower) catalogue scan is skipped for it — {@see DisplayWidth::of()} is a hot path.
     */
    private const string SEQUENCE_MARKERS = '/[\x{200D}\x{FE0F}\x{20E3}\x{1F1E6}-\x{1F1FF}\x{1F3FB}-\x{1F3FF}\x{E0020}-\x{E007F}]/u';

    private static ?Emojis $standalone = null;

    private static bool $disabled = false;

    public static function available(): bool
    {
        return ! self::$disabled && class_exists(Emojis::class);
    }

    /**
     * The catalogue glyph for a `:shortcode:` name, or null when the name is unknown or the
     * package is not installed.
     */
    public static function glyph(string $name, bool $unicode): ?string
    {
        return self::attempt(static function (Emojis $emojis) use ($name, $unicode): ?string {
            $emoji = $emojis->fromShortcode($name);

            if ($emoji === null) {
                return null;
            }

            return $emoji->render($unicode ? Mode::Emoji : Mode::Ascii);
        });
    }

    /**
     * Every shortcode the catalogue knows (empty when not installed).
     *
     * @return list<string>
     */
    public static function names(): array
    {
        return self::attempt(
            static fn (Emojis $emojis): array => array_map(strval(...), array_keys($emojis->catalogue()->shortcodeIndex())),
        ) ?? [];
    }

    /**
     * Rewrite literal Unicode emoji as `:shortcode:` text, so an ASCII-mode render can resolve
     * them like any other shortcode. Returns the input unchanged when not installed.
     */
    public static function toShortcodes(string $text): string
    {
        if (preg_match('/[^\x00-\x7F]/', $text) !== 1) {
            return $text;
        }

        return self::attempt(
            static fn (Emojis $emojis): string => $emojis->text($text)->from(Mode::Unicode)->to(Mode::Ascii),
        ) ?? $text;
    }

    /**
     * Terminal columns for decoration-free text, counting each emoji sequence as two. Null when
     * the text has no multi-codepoint emoji or the package is not installed.
     */
    public static function width(string $plain): ?int
    {
        if (preg_match(self::SEQUENCE_MARKERS, $plain) !== 1) {
            return null;
        }

        return self::attempt(static fn (Emojis $emojis): int => $emojis->text($plain)->from(Mode::Unicode)->width());
    }

    /**
     * Cut decoration-free text to a column budget without splitting an emoji sequence. Null
     * when the text has no multi-codepoint emoji or the package is not installed.
     */
    public static function truncate(string $plain, int $max): ?string
    {
        if (preg_match(self::SEQUENCE_MARKERS, $plain) !== 1) {
            return null;
        }

        return self::attempt(static fn (Emojis $emojis): string => $emojis->text($plain)->from(Mode::Unicode)->truncate($max, ''));
    }

    /**
     * Behave as though `laranail/emojis` were not installed. For tests exercising the fallback
     * in a process where the package is autoloadable.
     */
    public static function disable(): void
    {
        self::$disabled = true;
    }

    public static function reset(): void
    {
        self::$disabled = false;
        self::$standalone = null;
    }

    /**
     * @template T
     *
     * @param callable(Emojis): T $call
     *
     * @return T|null
     */
    private static function attempt(callable $call): mixed
    {
        if (! self::available()) {
            return null;
        }

        try {
            return $call(self::instance());
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * The application's configured instance when the package's provider has bound one, else a
     * standalone instance built once per process.
     */
    private static function instance(): Emojis
    {
        if (function_exists('app') && app()->bound(Emojis::class)) {
            /** @var Emojis */
            return app(Emojis::class);
        }

        return self::$standalone ??= Emojis::create();
    }
}
