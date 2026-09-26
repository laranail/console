<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * Computes the visible width a string occupies in a terminal — ignoring ANSI
 * escape sequences and accounting for wide (CJK/emoji) characters.
 *
 * All padding, centring and box geometry must route through this rather than
 * strlen(), or aligned output drifts.
 *
 * Symfony's width counts a multi-codepoint emoji by its parts — a ZWJ family is
 * 8 columns to it, 2 on screen. Text that can hold an emoji sequence is therefore
 * measured one grapheme cluster (PCRE `\X`) at a time, and a cluster carrying a
 * ZWJ, VS16, keycap, regional-indicator, skin-tone or tag codepoint counts as two
 * columns, per UTS #51. Checked against the full Unicode 18 catalogue of
 * `laranail/emojis`: this agrees on 3956 of 3972 emoji where Symfony alone agrees
 * on 1644. The 16 left are Unicode 17–18 codepoints newer than Symfony's width
 * table; when that package is installed it recognises them too (see
 * {@see EmojisBridge}), and the count reaches 3972. Everything else — the whole
 * of ASCII and CJK text — keeps the Symfony path unchanged.
 */
final class DisplayWidth
{
    /** A cluster containing any of these is an emoji sequence: two columns. */
    private const string SEQUENCE = '/[\x{200D}\x{FE0F}\x{20E3}\x{1F1E6}-\x{1F1FF}\x{1F3FB}-\x{1F3FF}\x{E0020}-\x{E007F}]/u';

    /**
     * Text containing none of these measures the same by cluster as by Symfony, so the cheaper
     * path is taken. The SEQUENCE markers, plus the emoji planes an older width table may miss.
     */
    private const string CLUSTER_CANDIDATES = '/[\x{200D}\x{FE0F}\x{20E3}\x{1F000}-\x{1FAFF}\x{E0020}-\x{E007F}]/u';

    /**
     * The UTF-8 lead bytes of every CLUSTER_CANDIDATES codepoint (E2: U+200D, U+20E3; EF: U+FE0F;
     * F0: U+1Fxxx; F3: U+E00xx). A byte scan for them is far cheaper than the regex, and rules out
     * ASCII and most other text before the regex runs.
     */
    private const string CANDIDATE_LEAD_BYTES = "\xE2\xEF\xF0\xF3";

    /**
     * Reused, stateless formatter for decoration stripping — `of()` runs for every
     * cell/pad/wrap, so building a fresh OutputFormatter per call was a real hot-path
     * cost (see benchmarks/DisplayWidthBench).
     */
    private static ?OutputFormatter $formatter = null;

    /**
     * Visible column width of a string (decoration stripped).
     */
    public static function of(string $text): int
    {
        $plain = Helper::removeDecoration(self::$formatter ??= new OutputFormatter, $text);

        return strpbrk($plain, self::CANDIDATE_LEAD_BYTES) === false ? Helper::width($plain) : self::measure($plain);
    }

    /**
     * Truncate to a visible width while preserving ANSI SGR colour and OSC-8
     * hyperlink sequences (neither counts toward the width) and closing anything
     * left open — so a clipped styled string never bleeds colour or leaves a
     * dangling hyperlink. No-op when the text already fits.
     */
    public static function truncateAnsi(string $text, int $max): string
    {
        if ($max <= 0) {
            return '';
        }

        if (self::of($text) <= $max) {
            return $text;
        }

        // Capture SGR (\e[…m) and OSC-8 hyperlink (\e]8;;…\e\) sequences as
        // zero-width passthrough tokens; everything else is visible text.
        $parts = preg_split('/(\e\[[0-9;]*m|\e\]8;;[^\e]*\e\\\\)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        $out = '';
        $width = 0;
        $sgrOpen = false;
        $linkOpen = false;

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if (str_starts_with($part, "\e]8;;")) {
                $out .= $part;
                $linkOpen = $part !== "\e]8;;\e\\"; // the empty-URL form is the close

                continue;
            }

            if (preg_match('/^\e\[[0-9;]*m$/', $part) === 1) {
                $out .= $part;
                $sgrOpen = $part !== "\e[0m";

                continue;
            }

            foreach (self::clusters($part) as $char) {
                $charWidth = self::clusterWidth($char);

                if ($width + $charWidth > $max) {
                    return $out . self::close($linkOpen, $sgrOpen);
                }

                $out .= $char;
                $width += $charWidth;
            }
        }

        return $out . self::close($linkOpen, $sgrOpen);
    }

    /**
     * The greatest visible width across the given lines (0 for an empty list).
     *
     * @param iterable<string> $lines
     */
    public static function maxWidth(iterable $lines): int
    {
        $max = 0;

        foreach ($lines as $line) {
            $max = max($max, self::of($line));
        }

        return $max;
    }

    /**
     * Pad a string on the right to a target visible width.
     */
    public static function pad(string $text, int $width, string $pad = ' '): string
    {
        $missing = $width - self::of($text);

        return $missing > 0 ? $text . str_repeat($pad, $missing) : $text;
    }

    /**
     * Pad a string on the left to a target visible width.
     */
    public static function padLeft(string $text, int $width, string $pad = ' '): string
    {
        $missing = $width - self::of($text);

        return $missing > 0 ? str_repeat($pad, $missing) . $text : $text;
    }

    /**
     * Truncate a (plain) string to a maximum visible width, accounting for
     * wide characters. Intended for decoration-free text such as titles.
     */
    public static function truncate(string $text, int $max): string
    {
        if ($max <= 0) {
            return '';
        }

        if (self::of($text) <= $max) {
            return $text;
        }

        $out = '';
        $width = 0;

        foreach (self::clusters($text) as $cluster) {
            $width += self::clusterWidth($cluster);

            if ($width > $max) {
                break;
            }

            $out .= $cluster;
        }

        return $out;
    }

    /**
     * Centre a string within a target visible width.
     */
    public static function center(string $text, int $width, string $pad = ' '): string
    {
        $missing = $width - self::of($text);

        if ($missing <= 0) {
            return $text;
        }

        $left = intdiv($missing, 2);

        return str_repeat($pad, $left) . $text . str_repeat($pad, $missing - $left);
    }

    /**
     * Width of decoration-free text.
     */
    private static function measure(string $plain): int
    {
        if (strpbrk($plain, self::CANDIDATE_LEAD_BYTES) === false || preg_match(self::CLUSTER_CANDIDATES, $plain) !== 1) {
            return Helper::width($plain);
        }

        $width = 0;

        foreach (self::clusters($plain) as $cluster) {
            $width += self::clusterWidth($cluster);
        }

        return $width;
    }

    /**
     * Width of one grapheme cluster: two for an emoji sequence, two for a single emoji Symfony's
     * table predates when a catalogue recognises it, Symfony's answer otherwise.
     */
    private static function clusterWidth(string $cluster): int
    {
        if (strlen($cluster) === 1) {
            return Helper::width($cluster);
        }

        if (preg_match(self::SEQUENCE, $cluster) === 1) {
            return 2;
        }

        $width = Helper::width($cluster);

        if ($width < 2 && mb_ord($cluster) >= 0x1F000 && EmojisBridge::isEmoji($cluster)) {
            return 2;
        }

        return $width;
    }

    /**
     * Split into extended grapheme clusters, so an emoji sequence is never cut apart. Falls back
     * to codepoints for input PCRE rejects (invalid UTF-8).
     *
     * @return list<string>
     */
    private static function clusters(string $text): array
    {
        return preg_match_all('/\X/u', $text, $matches) === false ? mb_str_split($text) : $matches[0];
    }

    /**
     * Close any open OSC-8 hyperlink + SGR style left by a truncation.
     */
    private static function close(bool $linkOpen, bool $sgrOpen): string
    {
        return ($linkOpen ? "\e]8;;\e\\" : '') . ($sgrOpen ? "\e[0m" : '');
    }
}
