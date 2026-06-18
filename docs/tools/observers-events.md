# Observers & Events

## ConsoleCommandObserver

`Simtabi\Laranail\Console\Tools\Observers\ConsoleCommandObserver` listens to
Laravel's console lifecycle (`CommandStarting`/`CommandFinished`) with
flexible matching — literal names, wildcards (`cache:*`), regex (`/^queue:/`),
callables, or arrays of any of those.

```php
use Simtabi\Laranail\Console\Tools\Observers\ConsoleCommandObserver;

ConsoleCommandObserver::for('migrate')
    ->onFinish(function ($event): void {
        // inspect $event / buffered output
    });

// Wildcards + an extra predicate
ConsoleCommandObserver::for('cache:*')
    ->when(fn ($input) => $input->getOption('force'))
    ->onStart(fn () => logger()->info('cache command starting'));
```

`ConsoleCommandObserver::fetchOutput($commandFinishedEvent)` retrieves the
buffered output of a finished command.

## CommandEvents

`Simtabi\Laranail\Console\Tools\Events\CommandEvents` is a self-contained
lifecycle event (no dependency on a host event base). The command base
dispatches it; you can also build it directly:

```php
use Simtabi\Laranail\Console\Tools\Events\CommandEvents;

$event = CommandEvents::starting($command, $input);      // action: 'starting'
$event = CommandEvents::terminating($command, $input, 0); // action: 'terminating', exitCode: 0
```

Public fields: `action`, `type`, `request`, `metadata`, `firedAt`,
`command`, `input`, `exitCode`.

---

[← Docs index](../../README.md#documentation)
