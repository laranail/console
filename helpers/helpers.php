<?php

declare(strict_types=1);

use Simtabi\Laranail\Console\Prompter\Prompter;

if (! function_exists('prompter')) {
    /**
     * Get a fresh Prompter instance (isolated $result per call).
     */
    function prompter(): Prompter
    {
        return Prompter::create();
    }
}
