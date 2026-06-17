# Architecture

`laranail/console-tools` groups the console layer into a few areas, all
under `Simtabi\Laranail\ConsoleTools`:

- **`Formatting/`** — `ConsoleUIFormatter` (fluent colors/badges/status
  lines/trees/statistics), `ConsoleProgressBar` (Symfony ProgressBar
  wrapper with memory tracking), `SeederConsoleFormatter` (+ contract) for
  tree-structured seeding output.
- **`Commands/`** — `LaranailCommand`, an enhanced Artisan base class that
  delegates to a `CommandServiceManager`.
- **`Commands/Services/`** — the nine command services (performance,
  events, signals, metadata, logging, errors, configuration, interaction,
  display) coordinated by `CommandServiceManager`.
- **`Observers/`** — `ConsoleCommandObserver` for command lifecycle hooks.
- **`Runners/`** — `ConsoleRunner` for conditional command execution.
- **`Events/`** — `CommandEvents` (self-contained command lifecycle event).
- **`Concerns/`** — `HasDisplayFormatting`, `HasConfigurationAccess`.

The package depends only on `illuminate/console`, `illuminate/support`,
`illuminate/contracts`, `laravel/prompts`, and `symfony/console` — no
dependency on the laranail core package.

---

[← Docs index](../README.md#documentation)
