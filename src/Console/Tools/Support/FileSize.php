<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

/**
 * Human-readable byte sizes (B / KB / MB / GB / TB).
 *
 * The single source of truth for byte formatting across the toolkit; the loop
 * scales only while the value is strictly greater than 1024, so exactly 1024
 * stays in the lower unit.
 */
final class FileSize
{
    private const array UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

    public static function format(int $bytes, int $precision = 2): string
    {
        $value = (float) $bytes;
        $i = 0;

        for (; $value > 1024 && $i < count(self::UNITS) - 1; $i++) {
            $value /= 1024;
        }

        return round($value, $precision) . ' ' . self::UNITS[$i];
    }
}
