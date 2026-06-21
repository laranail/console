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
        $this->services->display()->info('Starting…');

        $user = $this->services->interaction()->askText('Name?', default: 'admin');
        if (! $this->services->interaction()->askConfirm('Proceed?', true)) {
            return self::FAILURE;
        }

        $this->services->metadata()->add('synced', 42);

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
| `CommandMetadataService` | per-command metadata (`add()`, `get()`, `all()`) |
| `CommandLoggerService` | structured lifecycle logging |
| `CommandErrorService` | `executeWithErrorHandling()` / `executeWithFallback()` |
| `CommandConfigurationService` | cached config access (`get`, `getEnv`, `has`, `set`) |
| `CommandInteractionService` | Laravel Prompts wrappers (`askText`, `askConfirm`, `askSelect`, `showSpinner`) |
| `CommandDisplayService` | emoji status output, tables, progress bars |

The overridden `run()` starts timing, dispatches starting/finished events,
runs the command inside try/catch/finally, and ends the lifecycle, so
performance, events, and signals work without boilerplate.

The base is intentionally thin — it owns the lifecycle and a few verbosity
helpers (`isVerbose()`, `displayPerformanceSummary()`), and exposes everything
else through `$this->services` (`$this->getServices()` for callers outside the
command). Reach any service directly, e.g.
`$this->services->display()->info(...)`,
`$this->services->interaction()->askText(...)`,
`$this->services->metadata()->add(...)`. There are no per-method proxies on the
base — one obvious access path, no Middle-Man indirection.

## Use the trait (when you can't extend the base)

The base is just `extends Illuminate\Console\Command` + the
`Tools\Commands\Concerns\InteractsWithConsoleServices` trait. If your command must
extend a different base (a vendor command, Laravel's `GeneratorCommand`, …), `use`
the trait directly to get the **same** full support — `$this->services`, the managed
lifecycle, signals, structured exceptions and the verbosity helpers:

```php
use Illuminate\Console\GeneratorCommand;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\InteractsWithConsoleServices;

final class MakeWidgetCommand extends GeneratorCommand
{
    use InteractsWithConsoleServices;

    protected $signature = 'make:widget {name}';

    public function handle(): int
    {
        $this->services->display()->info('Generating…');

        return self::SUCCESS;
    }
}
```

Implement `handle()` as usual — the trait owns `run()`. `$this->services` is booted
lazily before `run()`; if you need it inside your own constructor, call
`$this->bootConsoleSupport()` after `parent::__construct()`.

For widgets/prompts in **any** class (no command, no inheritance at all), the
[`Console`](../../README.md) facade works everywhere: `Console::box(...)`,
`Console::table()`, `Console::prompter()->text(...)`.

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

This package ships its own `Tools\Commands\Concerns\SupportsNamespacedNames` trait
so commands can use the `laranail::<package-slug>.<command>` separator (Symfony's
validator otherwise rejects the empty `::` segment). `use` it on a command and set a
`::` name in the `$signature`:

```php
use Simtabi\Laranail\Console\Tools\Commands\Command;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\SupportsNamespacedNames;

final class SyncCommand extends Command
{
    use SupportsNamespacedNames;

    protected $signature = 'laranail::your-package.sync';
}
```

## `laranail::console.check`

Validates the `console.*` config and reports any problems, exiting non-zero on a bad
value (so it can gate CI/deploys):

```bash
php artisan laranail::console.check
```

It's the command form of `Console::validateConfig()` — see
[Configuration › Validation](../configuration.md).

---

[← Docs index](../../README.md#documentation)
