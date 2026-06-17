<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Providers;

use Illuminate\Support\ServiceProvider;
use Override;

/**
 * Service provider for laranail/console-tools.
 *
 * The package's classes are either static (ConsoleUIFormatter), instantiated
 * directly by consumers (ConsoleProgressBar, the command base + services), or
 * extended by consumers (LaranailCommand) — none require container bindings,
 * so this provider intentionally registers nothing today. It is kept for
 * auto-discovery stability and as the wiring point for any future bindings.
 */
final class ConsoleToolsServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        //
    }
}
