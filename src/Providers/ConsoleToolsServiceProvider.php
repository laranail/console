<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Providers;

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\ConsoleTools\Formatting\Contracts\SeederConsoleFormatterInterface;
use Simtabi\Laranail\ConsoleTools\Formatting\SeederConsoleFormatter;

final class ConsoleToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SeederConsoleFormatterInterface::class, static fn (): SeederConsoleFormatter => new SeederConsoleFormatter);
    }
}
