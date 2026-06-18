<?php

declare(strict_types=1);

use Simtabi\Laranail\Console\Prompter\Prompter;

if (! function_exists('prompter')) {
    /**
     * Resolve the shared Prompter instance.
     */
    function prompter(): Prompter
    {
        return Prompter::getInstance();
    }
}
