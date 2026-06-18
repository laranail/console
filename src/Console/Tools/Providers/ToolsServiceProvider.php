<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Providers;

use Illuminate\Support\ServiceProvider;
use Override;

/**
 * Child provider for the Tools sub-domain.
 *
 * Tools classes are either static (ConsoleUIFormatter), instantiated directly
 * (ConsoleProgressBar, widgets, the command base + services), or extended
 * (Command) — none require container bindings today. The class is the wiring
 * point for any future Tools bindings.
 */
final class ToolsServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        //
    }
}
