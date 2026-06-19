<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

/**
 * Reads `config('console.*')` defensively so the toolkit also works outside a
 * booted Laravel application (e.g. standalone scripts, tests) — returning the
 * given default when no config container is available.
 */
final class Config
{
    public static function get(string $key, mixed $default = null): mixed
    {
        if (function_exists('app') && app()->bound('config')) {
            return config("console.{$key}", $default);
        }

        return $default;
    }
}
