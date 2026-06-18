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
