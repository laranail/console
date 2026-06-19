# Commands

`Simtabi\Laranail\Console\Tools\Commands\Command` is an enhanced
Artisan command base with a service-based architecture. Extend it instead
of `Illuminate\Console\Command`.

```php
use Simtabi\Laranail\Console\Tools\Commands\Command;

final class SyncCommand extends Command
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
| `CommandConfigurationService` | cached config access (`get`, `getEnv`, `has`, `set`) |
| `CommandInteractionService` | Laravel Prompts wrappers (`askText`, `askConfirm`, `askSelect`, `showSpinner`) |
| `CommandDisplayService` | emoji status output, tables, progress bars |

The overridden `run()` starts timing, dispatches starting/finished events,
runs the command inside try/catch/finally, and ends the lifecycle, so
performance, events, and signals work without boilerplate.

Reach a service directly through the manager:
`$this->getServices()->display()` and `$this->getServices()->interaction()`.
The most common methods are also proxied onto the base command (`askText`,
`askConfirm`, `showProgressBar`, `infoMessage`, and so on).

## CommandDisplayService

`$this->getServices()->display()` formats output: emoji status messages,
tables, progress bars, byte sizes, and section headings. It needs a
`Symfony\Component\Console\Output\OutputInterface`, which the base command
wires up for you.

```php
$display = $this->getServices()->display();

$display->success('Migrated 42 rows');
$display->warning('Queue is backed up');
$display->error('Connection refused');
$display->info('Using the default profile');

$display->header('Import summary');                       // ruled heading
$display->displayTable(['Name', 'Rows'], [['users', 42]]);
$display->list(['users', 'posts'], 'Seeded tables');
$display->keyValue(['driver' => 'sqlite', 'rows' => 42]);
$display->separator();                                    // a rule line

$bar = $display->showProgressBar(count($rows), 'Importing');
echo $display->formatBytes(1048576);                      // "1 MB"
```

Methods: `success()`, `warning()`, `error()`, `info()`, `header()`,
`separator()`, `displayTable()`, `list()`, `keyValue()`, `showProgressBar()`,
`formatBytes()`.

## CommandInteractionService

`$this->getServices()->interaction()` wraps Laravel Prompts for input. Every
prompt honours non-interactive mode: call `setNonInteractive(true)` (or pass
`non_interactive` to `configureServices()`) and optional prompts return their
default instead of blocking, so commands stay scriptable in CI. A **required**
value with no usable default — notably `askPassword()` — throws a
`NonInteractiveException` rather than silently returning empty (toggle via
`console.interaction.non_interactive_required_throws`).

```php
$ask = $this->getServices()->interaction();

$name = $ask->askText('Project name', required: true);
$token = $ask->askPassword('API token');
$env  = $ask->askSelect('Environment', ['local', 'staging', 'production']);
$tags = $ask->askMultiSelect('Features', ['cache', 'queue', 'search']);

if ($ask->askConfirm('Run migrations?', default: true)) {
    // ...
}

$port = $ask->askWithValidation('Port', fn ($v) => ctype_digit($v), default: '8080');

$result = $ask->showSpinner('Syncing…', fn () => $this->sync());
```

Methods: `askText()`, `askPassword()`, `askConfirm()`, `askSelect()`,
`askMultiSelect()`, `askWithValidation()`, `confirmAction()`, `showSpinner()`,
`showLoading()`, `setNonInteractive()`, `isNonInteractive()`.

## Namespaced command names

To use the `laranail::<package-slug>.<command>` separator, extend the
package-tools base command or use its `SupportsNamespacedNames` trait — see
the [package-tools command-naming docs](https://opensource.simtabi.com/package-tools/docs/).

---

[← Docs index](../../README.md#documentation)
