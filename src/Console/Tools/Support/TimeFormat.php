<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

/**
 * Human-friendly, three-tier duration formatting:
 *   < 60s   -> "45.2s"
 *   < 1h    -> "2m 18s"
 *   >= 1h   -> "1h 8m"
 */
final class TimeFormat
{
    public static function duration(float $seconds): string
    {
        if ($seconds < 0) {
            $seconds = 0.0;
        }

        if ($seconds < 60) {
            return rtrim(rtrim(number_format($seconds, 1, '.', ''), '0'), '.') . 's';
        }

        if ($seconds < 3600) {
            $minutes = intdiv((int) $seconds, 60);
            $secs = (int) $seconds % 60;

            return "{$minutes}m {$secs}s";
        }

        $hours = intdiv((int) $seconds, 3600);
        $minutes = intdiv((int) $seconds % 3600, 60);

        return "{$hours}h {$minutes}m";
    }
}
