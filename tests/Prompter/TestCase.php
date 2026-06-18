<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Simtabi\Laranail\Console\Providers\ConsoleServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [ConsoleServiceProvider::class];
    }
}
