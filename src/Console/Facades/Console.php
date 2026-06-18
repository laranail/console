<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Facades;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Console\ConsoleManager;
use Simtabi\Laranail\Console\Prompter\Prompter;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

/**
 * @method static ConsoleUIFormatter ui()
 * @method static Prompter           prompter()
 *
 * @see ConsoleManager
 */
final class Console extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConsoleManager::class;
    }
}
