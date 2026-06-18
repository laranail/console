<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Providers;

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\Console\ConsoleManager;
use Simtabi\Laranail\Console\Prompter\Providers\PrompterServiceProvider;
use Simtabi\Laranail\Console\Tools\Providers\ToolsServiceProvider;

/**
 * Root service provider for laranail/console.
 *
 * Owns package-wide wiring — configuration, translations, the ConsoleManager
 * binding — and registers the per-sub-domain child providers. New sub-domains
 * are added by registering their child provider here.
 */
final class ConsoleServiceProvider extends ServiceProvider
{
    private const CONFIG_PATH = __DIR__ . '/../../../config/console.php';

    private const LANG_PATH = __DIR__ . '/../../../resources/lang';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'console');

        $this->app->singleton(ConsoleManager::class, static fn (): ConsoleManager => new ConsoleManager());

        $this->app->register(ToolsServiceProvider::class);
        $this->app->register(PrompterServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(self::LANG_PATH, 'console');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::CONFIG_PATH => $this->app->configPath('console.php'),
            ], 'console-config');

            $this->publishes([
                self::LANG_PATH => $this->app->langPath(),
            ], 'console-lang');
        }
    }
}
