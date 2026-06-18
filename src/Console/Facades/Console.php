<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Facades;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Console\ConsoleManager;
use Simtabi\Laranail\Console\Prompter\Prompter;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Widgets\ProgressBar;
use Simtabi\Laranail\Console\Tools\Widgets\Spinner;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method static ConsoleUIFormatter ui()
 * @method static Spinner spinner(string $message = '')
 * @method static ProgressBar progress(?OutputInterface $output = null, int $max = 0)
 * @method static Capabilities capabilities()
 * @method static Prompter prompter()
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
