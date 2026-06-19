# Changelog

All notable changes to `laranail/console` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-06-19

Initial release — a rich console toolkit for Laravel with two decoupled
sub-domains (`Console\Tools` for output, `Console\Prompter` for input) under one
namespace root. Targets PHP `^8.3` (8.3–8.5) on Laravel `^13.0`.

### Added

**Aggregator**

- `ConsoleManager` + the `Console` facade — a thin aggregator exposing both
  sub-domains (`ui()`, `prompter()`, `spinner()`, `progress()`, `box()`, `tree()`,
  `table()`, `gauge()`, `sparkline()`, `banner()`, `steps()`, `tasks()`,
  `status()`, `rule()`, `capabilities()`, `color()`). Global `prompter()` helper.

**Output (`Console\Tools`)**

- `Support\*` rendering backbone — `Capabilities` (single source of truth for
  terminal detection, honouring `NO_COLOR`/`FORCE_COLOR`/`TERM`/locale),
  `DisplayWidth`, `Symbols`, `BorderStyle`, `Color` (truecolor/hex + gradients,
  degrading truecolor → 256 → ANSI-16), and `TimeFormat`.
- `Widgets\*` — `Spinner`, a flavoured `ProgressBar` (percent/elapsed/ETA/rate,
  instance-scoped placeholders), `StatusLine`, `Rule`, `Box`, `Tree`,
  `Table` (with `grouped()` and `tree()` variants), `Callout`, `Banner`, `Gauge`,
  `Sparkline`, `StepFlow`, and a multi-task `TaskProgress` that exits non-zero on
  failure.
- An enhanced Artisan command base + conditional console runners, a console
  notification channel, and command lifecycle observers/events.

**Input (`Console\Prompter`)**

- `Prompter` — a fluent wrapper over `laravel/prompts` with a form builder and a
  total validator set (non-string input returns an error rather than throwing),
  including `Validators\LaravelRule` to bridge Illuminate validation rules. Each
  call resolves a fresh instance via `create()`.

**Packaging**

- Publishable `config/console.php` and `console::`-namespaced language files;
  `Exceptions\ConsoleException` base with `fromKey()` + safe fallback.

### Security

- All rendered text is stripped of terminal control characters (no ANSI/`\r`
  injection); terminal hyperlinks are limited to an allow-list of URL schemes.
- `PathFieldValidator` validates shape only (rejects traversal and null bytes, no
  filesystem-existence oracle); choice validators use strict comparison.
- Signal handling is guarded by `ext-pcntl` (commands run on Windows too).
- Sensitive keys are redacted and stack traces gated behind debug mode in logs.
