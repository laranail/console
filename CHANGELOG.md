# Changelog

All notable changes to `laranail/console` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

> The early rapid-iteration releases were consolidated into three milestone
> versions — **0.5.0** (foundation), **1.0.0** (first stable), and **2.0.0**
> (current) — each summarising the work of its line. The full granular commit
> history remains in git.

## [2.0.0] - 2026-06-22

Current stable. A breaking validator-message redesign plus a full QA-hardening pass.

### Changed

- **BREAKING — validator constructors take only domain arguments.** The failure
  message, translation replacements and locale moved from trailing constructor
  arguments to chainable methods — `->errorMessage()`, `->replace()`, `->locale()` —
  uniform across every validator, resolved lazily at validation time. Domain arguments
  (e.g. `StringFieldValidator(int $minLength, int $maxLength)`) are unchanged.
  `LaravelRule` drops its `explicitMessage`/`locale` constructor params in favour of the
  inherited fluent methods. Migration: [UPGRADING.md](UPGRADING.md).

### Added

- **QA hardening:** an enforced coverage gate, mutation testing (Infection, MSI 100 on
  logic code), a Windows + macOS CI matrix, and a real-app integration smoke test.
- Behavioural coverage for the fluent validator config (`->replace()` substitution,
  `->locale()` resolution) and an expanded Support-utilities / validators doc set.

## [1.0.0] - 2026-06-21

First SemVer-stable release, with the full 1.x hardening series folded in.

### Added

- **`Commands\Concerns\InteractsWithConsoleServices`** trait — the full managed command
  lifecycle (`$this->services`, signals, structured exceptions, verbosity helpers) usable
  on any `Illuminate\Console\Command`.
- **Config validation** — `Console::validateConfig()` + the **`laranail::console.check`**
  Artisan command, enabled by the new `Commands\Concerns\SupportsNamespacedNames` trait
  (allows the `::` command namespace). Opt-in fail-fast at boot.
- **Theme presets** — five built-in palettes (`dracula`, `nord`, `solarized`,
  `monochrome`, `github`) via `Theme::preset()` / `console.theme.preset`.
- **`Color::parseStrict()`**, the `StackedBar` chart, syntax highlighting for
  python/sql/html/css/diff, and a phpbench suite (`composer bench`).

### Changed

- **Public-API surface defined:** `@api` on the facades and `Renderable`/`Interactive`
  contracts; `@internal` on implementation-only classes (excluded from BC).
- **Length validators count characters, not bytes** (`mb_strlen`) — behaviour change for
  multibyte input.
- Performance: `DisplayWidth::of()` caches its formatter (~3× on the hot path); broad
  internal de-duplication (chart context, highlighter spec table, validator bases).

### Fixed

- Signal handling wires at `run()` time with a null-application guard (no longer fatals
  when a command is constructed outside a running Application).
- Hardening: clickable-link URLs are escaped against Symfony formatter-tag injection;
  `SyntaxHighlighter` bounds regex cost on pathological long lines.

## [0.5.0] - 2026-06-20

The foundational pre-1.0 build-out — a CLI **design system**: theme, typography, a
document composer, widgets/charts, and an interactive/live layer, all responsive and
degradation-safe, over two decoupled sub-domains (`Console\Tools` output,
`Console\Prompter` input).

### Added

- **Aggregator** — `ConsoleManager` + the `Console` facade exposing both sub-domains;
  global `prompter()` helper.
- **Rendering backbone** — `Support\*`: `Capabilities` (terminal/color/Unicode detection
  honouring `NO_COLOR`/`FORCE_COLOR`), `Color` (truecolor/hex/hsl + gradients, degrading
  truecolor → 256 → 16 → strip), `Style`, `DisplayWidth`, `Symbols`, `BrailleCanvas`,
  `Os`, `Align`, `Hyperlink`, ANSI primitives (`Sgr`/`Csi`/`ControlChars`).
- **Widgets** — spinner, progress/animated bars, status line, rule, box, tree, table
  (grouped/tree/collection variants), callout, banner (FIGlet via `Figlet`), gauge,
  sparkline, step flow, multi-task `TaskProgress` (ETA, non-zero on failure), key/value,
  columns, panels (nestable multi-column layout), and an interactive `Menu`.
- **Charts** — column, line, scatter, heatmap, histogram (braille/block, themed,
  responsive).
- **Typography & documents** — headings, paragraphs, lists, links, quotes, code blocks;
  `Document` composer + Markdown subset renderer (inline styling, tables, fenced-code
  highlighting).
- **Interactive & live layer** — `Support\Live` redraw engine, badges/pills/buttons,
  `Keypress`/`Terminal` readers; degrade safely on non-TTY.
- **Input** — `Prompter` (fluent `laravel/prompts` wrapper) with a form builder and the
  full validator suite, incl. `LaravelRule` bridging Illuminate rules.
- **Testing helpers** — `Testing\InteractsWithConsole`, `Capabilities::fake()`.
- **Packaging** — publishable `config/console.php` + `console::`-namespaced lang files;
  `ConsoleException` hierarchy.

### Security

- All rendered text is stripped of terminal control characters; hyperlinks limited to an
  allow-list of URL schemes. `PathFieldValidator` rejects traversal/null bytes (shape
  only). Signal handling guarded by `ext-pcntl`. Sensitive log keys redacted; traces
  gated behind debug mode.
