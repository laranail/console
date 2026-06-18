<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Providers;

use Illuminate\Support\ServiceProvider;
use Override;
use Simtabi\Laranail\Console\Prompter\Prompter;

/**
 * Child provider for the Prompter sub-domain.
 *
 * Package-wide concerns (config, translations, publishing) live in the root
 * ConsoleServiceProvider; this provider only registers Prompter bindings.
 */
final class PrompterServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(Prompter::class, static fn (): Prompter => Prompter::getInstance());
    }
}
