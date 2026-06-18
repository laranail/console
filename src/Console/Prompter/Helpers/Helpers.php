<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Helpers;

class Helpers
{
    public static function sanitizeInput(?string $input, ?string $default = ''): string
    {
        return !empty($input) ? trim($input) : $default;
    }
}
