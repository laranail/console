# Commands

`Simtabi\Laranail\ConsoleTools\Commands\LaranailCommand` is an enhanced
Artisan command base with a service-based architecture. Extend it instead
of `Illuminate\Console\Command`.

```php
use Simtabi\Laranail\ConsoleTools\Commands\LaranailCommand;

final class SyncCommand extends LaranailCommand
{
    protected $signature = 'app:sync';

    public function handle(): int
    {
        $this->infoMessage('Starting…');

        $user = $this->askText('Name?', default: 'admin');
        if (! $this->askConfirm('Proceed?', true)) {
            return self::FAILURE;
        }

        $this->addMetadata('synced', 42);

        return self::SUCCESS;
    }
}
```

## What the base provides

It delegates to a `CommandServiceManager` (`$this->getServices()`), composed
of nine focused services:

| Service | Responsibility |
|---|---|
| `CommandPerformanceService` | execution time + memory (`getMemoryUsage()`, `getPerformanceSummary()`) |
| `CommandEventService` | dispatch native + custom `CommandEvents` |
| `CommandSignalService` | SIGTERM/SIGINT graceful shutdown (`shouldKeepRunning()`, `stop()`) |
| `CommandMetadataService` | per-command metadata (`addMetadata()`, `getMetadata()`) |
| `CommandLoggerService` | structured lifecycle logging |
| `CommandErrorService` | `executeWithErrorHandling()` / `executeWithFallback()` |
| `CommandConfigurationService` | namespaced config access |
| `CommandInteractionService` | Laravel Prompts wrappers (`askText`, `askConfirm`, `askSelect`, `showSpinner`) |
| `CommandDisplayService` | emoji status output, tables, progress bars |

The overridden `run()` starts timing, dispatches starting/finished events,
runs the command inside try/catch/finally, and ends the lifecycle — so
performance/events/signals work without boilerplate.

## Namespaced command names

To use the `laranail::<package-slug>.<command>` separator, extend the
package-tools base command or use its `SupportsNamespacedNames` trait — see
the [package-tools command-naming docs](https://opensource.simtabi.com/package-tools/docs/).

---

[← Docs index](../README.md#documentation)
