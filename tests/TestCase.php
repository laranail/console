<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Simtabi\Laranail\ConsoleTools\Providers\ConsoleToolsServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [ConsoleToolsServiceProvider::class];
    }

    /**
     * @param Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
    }
}
