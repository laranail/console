# Changelog

All notable changes to `laranail/console` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.1] - 2026-09-26

### Added

- **A shared status vocabulary, `Tools\Support\Status`.** It has nine cases (`Success`, `Failed`,
  `Warning`, `Pending`, `Running`, `Skipped`, `Active`, `Inactive`, `Unknown`). Each case carries its
  glyph (Unicode or ASCII, via `Symbols`), colour, palette role and a translated label under
  `laranail-console::console.status.*`. `TaskStatus::toStatus()` maps task states onto it.
- **`StatusBadge`**: an inline status label (`✓ Completed`) built from a `Status` or a boolean.
  `StatusBadge::fromMap()` maps a domain value onto the vocabulary, and an unmapped value renders as
  `Unknown` instead of throwing. Pass `valueAsLabel: true` to show the domain value itself
  (`✓ up-to-date`).
- **`CheckList`**: an aligned readiness checklist (`✓ Tables: OK`, `✗ Seeded: NOT READY`) with a
  `passes()` summary.
- **`MetricTable`**: the two-column `Metric | Value` table, with translated headers and grouped
  integers. `metrics()` also takes a list of `[label, value]` pairs, so two rows with identical
  labels are both kept.
- **`Support\ExceptionRenderer`**: prints the message always, the file and line from `-v`, and the
  stack trace only from `-vvv`.
- **`Commands\Concerns\ConfirmsDestructiveActions`**: `confirmDestructive()` answers yes under
  `--force` without prompting, and otherwise defaults to no. The yes and no labels can be
  overridden for each action. `cancelled()` reports the refusal and
  returns a success exit code.
- Facade accessors `Console::statusBadge()`, `Console::checkList()` and `Console::metricTable()`.

### Changed

- **`Table::render($output)` renders with the target output's decoration.** It used an
  undecorated buffer, so markup inside a cell (a coloured status badge) lost its colour even on a
  TTY. Piped output is still plain.
- **`handleException()` now delegates to `ExceptionRenderer`.** The verbosity policy is unchanged.
  Exception messages are now sanitised before they are written, so an escape sequence carried inside
  a message can no longer drive the terminal.
- **A command's `$commandAliases` is read defensively** by both the `Command` base and
  `SupportsNamespacedNames`, rather than being declared on the base. A consuming command can declare
  its own list without a composition fatal, and a command that declares none no longer throws
  `Undefined property` at construction.

## [0.1.0] - 2026-08-15

### Changed

- **The config key is `laranail.console`,** published to `config/laranail/console.php`. Every read
  moves with it — `config('console.theme.preset')` is now
  `config('laranail.console.theme.preset')`. Laravel's config repository is a flat map and `console`
  is a name an application could plausibly use for its own file.

- **Publish tags are vendor-scoped:** `console-config` → `laranail::console-config`, `console-lang`
  → `laranail::console-lang`.

### Fixed

- **Published translations went to the lang root** rather than to
  `lang/vendor/laranail-console`, which is where the namespaced loader looks — so every published
  override was silently ignored while the packaged default kept answering.

Initial public release.
