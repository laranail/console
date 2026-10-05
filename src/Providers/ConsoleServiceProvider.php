<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Providers;

use Override;
use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\Console\ConsoleManager;
use Simtabi\Laranail\Console\Tools\Support\Translations;
use Simtabi\Laranail\Console\Exceptions\ConsoleException;
use Simtabi\Laranail\Console\Tools\Support\ConfigValidator;
use Simtabi\Laranail\Console\Tools\Providers\ToolsServiceProvider;
use Simtabi\Laranail\Console\Prompter\Providers\PrompterServiceProvider;

/**
 * Root service provider for laranail/console.
 *
 * Owns package-wide wiring — configuration, translations, the ConsoleManager
 * binding — and registers the per-sub-domain child providers. New sub-domains
 * are added by registering their child provider here.
 *
 * @internal Auto-discovered framework wiring; not part of the public API.
 */
final class ConsoleServiceProvider extends ServiceProvider
{
    private const string CONFIG_PATH = __DIR__ . '/../../config/console.php';

    private const string LANG_PATH = __DIR__ . '/../../resources/lang';

    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'laranail.console');

        $this->app->singleton(ConsoleManager::class, static fn (): ConsoleManager => new ConsoleManager);

        $this->app->register(ToolsServiceProvider::class);
        $this->app->register(PrompterServiceProvider::class);
    }

    public function boot(): void
    {
        // The canonical namespace is the composer package name, so overrides land in
        // lang/vendor/laranail/console. The hyphen form is the pre-0.1.5 namespace,
        // kept over the same files so `__('laranail-console::…')` still resolves;
        // Translations::get() also honours overrides a host made against it.
        $this->loadTranslationsFrom(self::LANG_PATH, Translations::NAMESPACE);
        $this->loadTranslationsFrom(self::LANG_PATH, Translations::LEGACY_NAMESPACE);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::CONFIG_PATH => $this->app->configPath('laranail/console.php'),
            ], 'laranail::console-config');

            // vendor/laranail/console, matching the canonical namespace above
            // (Laravel reads overrides from lang/vendor/{namespace}). Publishing to
            // the lang root put the files where the namespaced loader never looks.
            $this->publishes([
                self::LANG_PATH => $this->app->langPath('vendor/' . Translations::NAMESPACE),
            ], 'laranail::console-lang');

            // Opt-in fail-fast: validate console.* config at boot (console only, so
            // web requests are never affected). Off by default.
            if ((bool) config('laranail.console.validate_config', false)) {
                $errors = ConfigValidator::validate();

                if ($errors !== []) {
                    throw new ConsoleException(
                        "Invalid laranail/console configuration:\n  - " . implode("\n  - ", $errors),
                    );
                }
            }
        }
    }
}
