# Runners

Conditional execution helpers under `Simtabi\Laranail\ConsoleTools\Runners`.

## ConsoleRunner

Runs a callback only when a fluent set of conditions holds. `ConsoleRunner`
starts with a built-in "running in console" guard, plus command /
maintenance / scheduled / verbosity conditions.

```php
use Simtabi\Laranail\ConsoleTools\Runners\ConsoleRunner;

ConsoleRunner::make()
    ->whenNotInMaintenance()
    ->whenCommand(['migrate', 'db:seed'])
    ->run(function (): void {
        // only runs in console, outside maintenance, for those commands
    });

// Return a value, with a fallback when skipped:
$result = ConsoleRunner::make()
    ->expectReturn('skipped')
    ->whenCommand('app:sync')
    ->run(fn (): string => 'ran');
```

## BaseRunner

The abstract base behind `ConsoleRunner`. It provides the generic condition
DSL (`when`, `unless`, `whenEnvironment`, `whenConfig`, `whenFeature`,
`whenExists`, `whenNotEmpty`, `whenTruthy`), lifecycle callbacks (`before`,
`after`, `onSuccess`, `onError`, `finally`, `whenSkipped`), optional
condition/execution logging, `expectReturn()`, `runOr()`, and `debug()`.
Subclass it and implement `initialize()` to ship your own default
conditions.

---

[← Docs index](../../README.md#documentation)
