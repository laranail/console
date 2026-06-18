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
        // bind (not singleton): each resolution is an isolated Prompter so the
        // fluent $result never bleeds across callers (incl. Octane workers).
        $this->app->bind(Prompter::class, static fn (): Prompter => Prompter::create());
    }
}
