# Architecture

`laranail/console-tools` groups the console layer into a few areas, all
under `Simtabi\Laranail\ConsoleTools`:

- **`Formatting/`** — `ConsoleUIFormatter` (fluent colors/badges/status
  lines/trees/statistics) and `ConsoleProgressBar` (Symfony ProgressBar
  wrapper with memory tracking).
- **`Commands/`** — `LaranailCommand`, an enhanced Artisan base class that
  delegates to a `CommandServiceManager`.
- **`Commands/Services/`** — the nine command services (performance,
  events, signals, metadata, logging, errors, configuration, interaction,
  display) coordinated by `CommandServiceManager`.
- **`Observers/`** — `ConsoleCommandObserver` for command lifecycle hooks.
- **`Runners/`** — `BaseRunner` + `ConsoleRunner` for conditional execution.
- **`Events/`** — `CommandEvents` (self-contained command lifecycle event).
- **`Notifications/`** — a standalone `ConsoleChannel` (+ minimal contract).

The package depends only on `illuminate/console`, `illuminate/support`,
`illuminate/contracts`, `laravel/prompts`, and `symfony/console` — no
dependency on the laranail core package, and nothing seeding-related.

---

[← Docs index](../README.md#documentation)
