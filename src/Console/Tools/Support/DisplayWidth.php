<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;

/**
 * Computes the visible width a string occupies in a terminal — ignoring ANSI
 * escape sequences and accounting for wide (CJK/emoji) characters.
 *
 * All padding, centring and box geometry must route through this rather than
 * strlen(), or aligned output drifts.
 */
final class DisplayWidth
{
    /**
     * Visible column width of a string (decoration stripped).
     */
    public static function of(string $text): int
    {
        return Helper::width(Helper::removeDecoration(new OutputFormatter, $text));
    }

    /**
     * Truncate to a visible width while preserving ANSI SGR sequences (they don't
     * count toward the width) and closing any style left open, so a clipped styled
     * string never bleeds colour. No-op when the text already fits.
     */
    public static function truncateAnsi(string $text, int $max): string
    {
        if ($max <= 0) {
            return '';
        }

        if (self::of($text) <= $max) {
            return $text;
        }

        $parts = preg_split('/(\e\[[0-9;]*m)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        $out = '';
        $width = 0;
        $open = false;

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if (preg_match('/^\e\[[0-9;]*m$/', $part) === 1) {
                $out .= $part;
                $open = $part !== "\e[0m";

                continue;
            }

            foreach (mb_str_split($part) as $char) {
                $charWidth = self::of($char);

                if ($width + $charWidth > $max) {
                    return $open ? $out . "\e[0m" : $out;
                }

                $out .= $char;
                $width += $charWidth;
            }
        }

        return $open ? $out . "\e[0m" : $out;
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

        foreach (mb_str_split($text) as $char) {
            if (self::of($out . $char) > $max) {
                break;
            }

            $out .= $char;
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
}
