# Changelog

All notable changes to `laranail/console` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
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
