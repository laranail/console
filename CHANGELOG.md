# Changelog

All notable changes to `laranail/console` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- **Prompter dispatch**: prompt methods (`text`, `select`, …) and context methods
  (`note`, `warning`, …) now dispatch correctly through the fluent `Prompter`;
  previously every call threw because the dispatcher used `method_exists()` on the
  closure-map-backed `PromptService`.
- `ProgressBar` no longer registers its custom placeholders on Symfony's
  process-wide defaults (instance-scoped), so unrelated progress bars are
  unaffected.
- `Box`/`Rule` treat a fixed `width()` as a minimum and never overflow the frame;
  ASCII tree connectors align; `badMethodCall` resolves the correct lang key.

### Changed
- Unified terminal capability detection through `Tools\Support\Capabilities`
  (single source of truth) and a shared `Tools\Support\Config` accessor.
- `Prompter` resolves a fresh instance per call/chain (no shared `$result` state);
  `getInstance()` is deprecated in favour of `create()`.
- `Color` degrades truecolor → 256-colour → ANSI-16; `Capabilities::width()` falls
  back to the real terminal.

### Added
- `Table` gains `grouped()` and `tree()` variants; validators honour
  `console.locale`; new `docs/tools/support.md`; the examples are smoke-tested in CI.

### Removed
- Dead `Formatting\ConsoleProgressBar` (superseded by the `ProgressBar` widget),
  the unused `Enums\UUIDType`, and foreign `laranail.core/installer/updater`
  config accessors.

## [0.1.0] - 2026-06-18

### Added
- Merged the console output toolkit and the prompts/forms toolkit into a single
  `laranail/console` package with two decoupled sub-domains, `Console\Tools`
  and `Console\Prompter`, under one namespace root. Targets PHP `^8.3` (8.3–8.5)
  on Laravel `^13.0`.
- `ConsoleManager` + `Console` facade — a thin aggregator exposing both
  sub-domains (`ui()`, `prompter()`, `spinner()`, `progress()`, `box()`,
  `tree()`, `table()`, `gauge()`, `sparkline()`, `banner()`, `steps()`,
  `tasks()`, `status()`, `rule()`, `capabilities()`, `color()`).
- `Tools\Support\*` — `Capabilities`, `DisplayWidth`, `Symbols`, `BorderStyle`,
  `Color` (truecolor/hex + gradients) and `TimeFormat`: the shared rendering
  backbone.
- `Tools\Widgets\*` — `Spinner`, a flavoured `ProgressBar` (percent/elapsed/ETA/
  rate), `StatusLine`, `Rule`, `Box`, `Tree`, `Table`, `Callout`, `Banner`,
  `Gauge`, `Sparkline`, `StepFlow`, and a multi-task `TaskProgress` (with `Task`
  and `TaskStatus`) that exits non-zero on failure.
- Publishable `config/console.php` and `console::`-namespaced language files.
- `Console\Exceptions\ConsoleException` base with `fromKey()` + safe fallback;
  `PrompterException` reparented onto it; new Tools exceptions.
- `Prompter\Validators\LaravelRule` — bridge Illuminate validation rules into a
  prompt validator. Global `prompter()` helper.

### Security
- Strip terminal control characters from all rendered text (ANSI/CR injection)
  and allow-list URL schemes for terminal hyperlinks.
- `ConsoleChannel` pins the notification level to a safe set and escapes content.
- Validators are total — non-string input returns an error instead of throwing.
- `PathFieldValidator` validates shape only (no filesystem existence oracle;
  rejects traversal and null bytes); choice validators use strict comparison.
- Redact sensitive keys and gate stack traces behind debug mode when logging.
- A required value requested non-interactively throws instead of returning empty.

### Fixed
- The form builder mapped field types to non-existent prompt methods and spread
  unsupported named arguments; both could fatal. Field types now map to real
  `Laravel\Prompts\FormBuilder` methods with per-method argument sets.
- Signal handling is guarded by `ext-pcntl`, so constructing a command no longer
  fatals on platforms without it (e.g. Windows), and signals are now actually
  registered.
- The global `prompter()` helper was declared inside a namespace while guarding
  on the global name, so it never existed; it is now genuinely global.
