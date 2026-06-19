<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

/**
 * 24-bit (truecolor) and hex colouring with graceful degradation.
 *
 * When the terminal lacks truecolor the text is returned unstyled rather than
 * emitting escape codes a terminal can't interpret. Colour output is also
 * suppressed entirely when {@see Capabilities::supportsColor()} is false, so
 * NO_COLOR and non-TTY pipes stay clean.
 */
final readonly class Color
{
    private Capabilities $capabilities;

    public function __construct(?Capabilities $capabilities = null)
    {
        $this->capabilities = $capabilities ?? Capabilities::detect();
    }

    public static function make(?Capabilities $capabilities = null): self
    {
        return new self($capabilities);
    }

    public static function isValidHex(string $hex): bool
    {
        return preg_match('/^#?[0-9a-fA-F]{6}$/', $hex) === 1;
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Wrap text in a foreground hex colour.
     */
    public function fg(string $text, string $hex): string
    {
        if (! $this->capabilities->supportsColor() || ! self::isValidHex($hex)) {
            return $text;
        }

        [$r, $g, $b] = self::hexToRgb($hex);

        return $this->openSgr($r, $g, $b) . $text . "\033[0m";
    }

    /**
     * Interpolate a per-character foreground gradient across colour stops.
     *
     * @param list<string> $stops Hex colours (≥ 2)
     */
    public function gradient(string $text, array $stops): string
    {
        $chars = mb_str_split($text);
        $count = count($chars);

        if (! $this->capabilities->supportsColor() || count($stops) < 2 || $count === 0) {
            return $text;
        }

        $rgbStops = array_values(array_map(self::hexToRgb(...), $stops));
        $segments = count($rgbStops) - 1;
        $out = '';

        foreach ($chars as $i => $char) {
            $t = $count > 1 ? $i / ($count - 1) : 0.0;
            $scaled = $t * $segments;
            $idx = min((int) $scaled, $segments - 1);
            $local = $scaled - $idx;

            [$r1, $g1, $b1] = $rgbStops[$idx];
            [$r2, $g2, $b2] = $rgbStops[$idx + 1];

            $r = (int) round($r1 + ($r2 - $r1) * $local);
            $g = (int) round($g1 + ($g2 - $g1) * $local);
            $b = (int) round($b1 + ($b2 - $b1) * $local);

            $out .= $this->openSgr($r, $g, $b) . $char;
        }

        return $out . "\033[0m";
    }

    /**
     * Opening SGR sequence for an RGB colour at the best available depth:
     * 24-bit truecolor → xterm 256 → nearest ANSI-16.
     */
    private function openSgr(int $r, int $g, int $b): string
    {
        if ($this->capabilities->supportsTrueColor()) {
            return "\033[38;2;{$r};{$g};{$b}m";
        }

        if ($this->capabilities->supports256Color()) {
            return "\033[38;5;" . $this->rgbTo256($r, $g, $b) . 'm';
        }

        return "\033[" . (30 + $this->nearestAnsi($r, $g, $b)) . 'm';
    }

    /**
     * Quantize an RGB triple to the xterm 256-colour palette (6×6×6 cube +
     * grayscale ramp).
     */
    private function rgbTo256(int $r, int $g, int $b): int
    {
        if ($r === $g && $g === $b) {
            if ($r < 8) {
                return 16;
            }

            if ($r > 248) {
                return 231;
            }

            return 232 + (int) round(($r - 8) / 247 * 24);
        }

        return 16
            + 36 * (int) round($r / 255 * 5)
            + 6 * (int) round($g / 255 * 5)
            + (int) round($b / 255 * 5);
    }

    /**
     * Nearest of the 8 basic ANSI colours (0-7) for an RGB triple.
     */
    private function nearestAnsi(int $r, int $g, int $b): int
    {
        $palette = [
            0 => [0, 0, 0], 1 => [205, 0, 0], 2 => [0, 205, 0], 3 => [205, 205, 0],
            4 => [0, 0, 238], 5 => [205, 0, 205], 6 => [0, 205, 205], 7 => [229, 229, 229],
        ];

        $best = 7;
        $bestDist = PHP_INT_MAX;

        foreach ($palette as $code => [$pr, $pg, $pb]) {
            $dist = ($r - $pr) ** 2 + ($g - $pg) ** 2 + ($b - $pb) ** 2;

            if ($dist < $bestDist) {
                $bestDist = $dist;
                $best = $code;
            }
        }

        return $best;
    }
}
