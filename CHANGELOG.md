# Changelog

All notable changes to `laranail/console` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.6.1] - 2026-06-20

Pre-open-source QA: one bug fix plus documentation-accuracy and hygiene polish.

### Fixed

- `Support\DisplayWidth::truncateAnsi()` corrupted OSC-8 hyperlinks — it only
  recognised SGR sequences, so a clipped link (e.g. a `Link` inside a narrow
  `Panel`/`Summary`) was cut mid-introducer, dropping the label/URL and leaving a
  dangling hyperlink. OSC-8 sequences are now zero-width passthrough and any open
  link/style is closed on truncation.
- Documentation accuracy: removed a non-existent `Box::content()` (added the real
  `responsive()`), fixed a broken `responsive.md` back-link, completed the
  `FieldType` case list and the validator count/table (incl. `LaravelRule`), and
  corrected the service-count and a class reference.

### Changed

- `composer.json` `branch-alias` `0.2.x-dev` → `0.x-dev`; Dependabot timezone
  `UTC` → `America/New_York`.

## [0.6.0] - 2026-06-20

Clears the items deferred from v0.5.

### Added

- **Inline Markdown styling** — `Document\InlineMarkup` renders `**bold**`,
  `*italic*`, `` `code` `` and `[label](url)` (+ `:emoji:`) to themed ANSI;
  `Paragraph::rich()` wraps already-styled text (ANSI-aware, per-line reset).
  Markdown paragraphs now render with real emphasis (degrade to plain without colour).
- **Basic code highlighting** — `CodeBlock::language()` + `Typography\SyntaxHighlighter`
  for **php** and **json** (others render plain); Markdown fenced blocks use their
  info-string.
- **`DisplayWidth::truncateAnsi()`** — width-truncate while preserving and closing
  ANSI styling.
- New examples (markdown, typography, theming, barchart, emoji, symbols) and doc
  pages (markdown, barchart, emoji, symbols).

### Changed

- Responsiveness now also covers `Summary` content lines (clamped via
  `truncateAnsi`) and `Panel` horizontal layouts (block widths shrink to fit the
  terminal; `->responsive(false)` opts out).

## [0.5.1] - 2026-06-20

### Changed

- Width-responsiveness now also covers `KeyValue`, `Tree` (rows clip to the
  terminal on overflow) and `Summary` (divider clamps to the terminal); each
  honours `config('console.responsive')` and `KeyValue`/`Tree` add `->responsive()`.

### Documentation

- New pages: theming, colours & styles, typography, responsive output, and the
  interactive & live layer; README docs index updated.

## [0.5.0] - 2026-06-20

A CLI **design system**: theme + typography + document composer + an interactive/
live layer, all responsive and degradation-safe.

### Added

- **Design tokens** — `Theme\Theme` (a stylesheet: semantic `Palette` + per-element
  styles) + `config('console.theme.palette')`; `Console::theme()`. Re-skin the whole
  UI from one palette.
- **`Support\Style`** — an immutable, chainable text style (fg/bg + bold/dim/italic/
  underline/strikethrough/inverse/blink) with graceful colour downgrade.
- **Typography** (`Tools\Typography`) — `Heading` (1–6), `Paragraph` (wrap/center/
  right/justify), `ListBlock` (unordered/ordered/task/definition), `Link`, `Quote`,
  `BlockQuote`, `Code`, `CodeBlock`, and a fluent inline `Text` builder.
- **Document** (`Tools\Document`) — `Document` fluent page composer + `Markdown`
  subset renderer (`Console::document()`, `Console::markdown()`).
- **Interactive & live layer** — `Support\Live` (native redraw engine, TTY-guarded),
  `AnimatedBar`, `Badge`, `Pill`, `Button`, `ButtonGroup` (interactive choice via
  laravel/prompts), and a `Contracts\Interactive` marker.
- **`BarChart`** widget; **banner themes** (`Banner::theme()/success()/error()/…` +
  `config('console.banner.themes.*')`).
- **Primitives** — `Color` gains `rgb()/hsl()/named/@256` parsing, `bg()`,
  `sequence()`, `blend()`, `adaptive()`; `Support\Os` (platform/WSL/CI detection),
  `Support\Align`, `Support\ResponsiveWidth`, `Support\Hyperlink`; `Console::style()/
  symbol()/os()/text()/paragraph()/heading()/list()/link()/quote()/blockQuote()/
  code()/codeBlock()/badge()/pill()/button()/buttonGroup()/live()/animatedBar()/
  barChart()`.
- `config('console.responsive')`.

### Changed

- **Width-responsiveness is on by default**: `Box` (and `Callout`/`Banner`) and
  `Table` clamp to the terminal so content never overflows; an explicit `->width()`
  wins, `->responsive(false)` opts out, `config('console.responsive')` toggles
  globally. The new design-system layer is responsive throughout. Wide-terminal
  output for existing widgets is unchanged.
- `Color` colour-profile downgrade (truecolor → 256 → 16 → strip) was already
  present; extended with the new parsers + background sequences (BC: `fg()`/
  `gradient()` unchanged). `ConsoleUIFormatter` link security now delegates to
  `Support\Hyperlink` (no behaviour change).

### Removed

- `Prompter::getInstance()` (deprecated; use `Prompter::create()` / the `prompter()`
  helper / the `Prompter` facade).

## [0.4.0] - 2026-06-19

### Added

- **Testing helpers** — `Testing\InteractsWithConsole` (`withConsoleCapabilities()`
  + `withPromptInput()`) and `Support\Capabilities::fake()` / `clearFake()`, so
  downstream suites can force a deterministic terminal profile and script prompts.
  See `docs/tools/testing.md`.
- **Translatable widget strings** — `Summary`, `Header`, `Menu`, `TaskProgress`
  and `Callout` resolve their text from `console::console.widgets.*` via a new
  `Support\Lang` helper, honouring `config('console.locale')` without touching the
  app's global locale. English output is unchanged.
- `config('console.summary.*')` — tune the execution-summary layout (divider
  width, label padding, message truncation, success-rate colour thresholds).
- `Support\DisplayWidth::maxWidth()`.

### Changed

- **The enhanced `Command` base is slimmed to its lifecycle.** The ~31 one-line
  "middle-man" pass-through helpers (`addMetadata`, `askText`, `getExecutionTime`,
  `warning`, …) are removed; reach the services directly via
  `$this->services->{service}()->…`. No production class extended the base
  (`AbstractPrompterCommand` extends Illuminate directly), so no capability is
  lost. The nine services keep their full public APIs.
- The six regex validators now share a `RegexValidator` base; the 25 leaf
  validators are marked `final` (command services stay extensible).
- `Box`/`Banner`/`Table`/`Rule` resolve capabilities via `Capabilities::detect()`
  (consistent with the other widgets; lets test fakes propagate). Production
  behaviour unchanged.

### Removed

- Dead code: `Symbols::usesUnicode()`, `TaskStatus::isTerminal()`, `Box::content()`.

## [0.3.0] - 2026-06-19

### Security

- `Table` now sanitises every header/cell/group-label/tree-cell at render time
  (was only `fromAssoc()`/`cell()`), closing terminal-control-character injection
  from attacker-controlled rows.
- `ConsoleUIFormatter::addTextColor()` routes its `href` through the OSC-8 scheme
  allow-list (a hostile URL can no longer emit an arbitrary hyperlink).
- Removed the dead, unredacted `CommandLoggerService::logError()` and other unused
  log methods (errors are logged via the hardened `CommandErrorService`).
- `Figlet` rejects null-byte font paths; `RenderableWidget` sanitises raw strings.

### Changed

- **`symfony/tui` is now OPTIONAL** (moved to `suggest` + `require-dev`). The core
  installs **stably** — consumers no longer inherit a dev-stability requirement.
  `composer require symfony/tui` enables `Console::tui()`; without it that method
  throws a clear `ConsoleException`. PHP floor stays `^8.4.1`.
- `Support\FileSize` scales at each 1024 boundary (`1024 B` → `1 KB`, `1 MiB` →
  `1 MB`); it is now the single byte formatter (perf + display services agree).
- Rector pinned to the `php84` set.

### Added

- `Widgets\KeyValue` (`Console::keyValue()`) — an aligned, sanitised definition list.
- `Table::fromCollection()` — ingest a Laravel Collection / iterable of assoc rows.
- `Console::summary()` / `Console::header()` accessors (+ facade `@method`).
- `Tools\Exceptions\FontException` — Figlet failures route through the exception
  hierarchy with `console::console.font_*` messages.
- The four orphaned validators are wired into `FieldType` (boolean/name/string/json).

### Removed

- Dead code: `ConsoleUIFormatter` session/statistics block + `configure()`; the
  `PromptService` closure map (the generic prompts forwarder covers it); dead
  `ContextType`/`FieldType` enum helper methods.

## [0.2.1] - 2026-06-19

### Fixed

- **TUI bridge** — `Console\Tui\RenderableWidget::render()` now clips lines to the
  `RenderContext` width (display-width aware) and caps rows, so mounting a wide
  widget no longer triggers symfony/tui's `RenderException`.
- **`ConsoleUIFormatter::colorize()`** — background tokens (`BG_*`) now resolve to
  the `*_bg` ANSI codes instead of foreground colours.
- **Prompter `context()` helpers** — `warning`/`error`/`alert`/`info`/`intro`/`outro`
  dispatch the correct `laravel/prompts` helper instead of rendering a plain note.
- `ConsoleUIFormatter::render()` no longer discards a literal `"0"` (zero-count
  `Summary` badges render `0`).

### Added

- `Console::summary()` and `Console::header()` accessors (+ facade `@method`) for
  the `Summary`/`Header` widgets.

### Changed

- Rector pinned to the `php84` set (matching the 8.4.1 floor); dropped the stale
  8.4→8.3 downgrade rule and applied the 8.4 modernizations.
- `composer.json` `branch-alias` → `0.2.x-dev`; `menu.php`/`tui.php` added to the
  examples smoke; docs cross-link + signature fixes.

## [0.2.0] - 2026-06-19

### Changed — BREAKING

- **Minimum PHP is now `^8.4.1`** (dropped 8.3) and Symfony deps are **`^8.0`
  only**, to natively integrate the experimental `symfony/tui`. The package sets
  `minimum-stability: dev` + `prefer-stable: true`; consuming apps must do the same.

### Added

- **Full-screen TUI** — integrates `symfony/tui` (MIT, experimental):
  `Console\Tui\RenderableWidget` mounts any of our widgets into a
  `Symfony\Component\Tui\Tui` app; `Console::tui()` returns a ready app. See
  `docs/tools/tui.md`.
- **Table completeness** — `Table` gains `align()`, `columnWidths()`/
  `maxColumnWidth()`, `title()`/`footer()`, `fromAssoc()`, and a `Table::cell()`
  factory for per-cell alignment/colour.
- **`Widgets\Columns`** (`Console::columns()`) — flow a flat list into balanced,
  width-aware columns (auto-fits the terminal).
- **Full Prompter parity** — the `Prompter` now forwards to any `laravel/prompts`
  helper (number, clear, autocomplete, datatable, grid, task, notify, title,
  stream, …) and auto-tracks new ones; `laravel/prompts` bumped to `^0.3.18|^1.0`.
- `Spinner::elapsed()` shows elapsed time in manual mode; `Tree::fromArray()`
  builds a tree from a nested array; Menu radios support independent `group`s.
- **Interactive menu** — `Widgets\Menu\*` (`Console::menu()` + a `Command::macro('menu')`):
  a native key-driven menu (options, checkboxes, radios, sub-menus, static items,
  free-text questions) with a `laravel/prompts` fallback for non-TTY/Windows. No
  `php-school/cli-menu` dependency. Configurable via `config('console.menu.*')`.
  Mirrors the nunomaduro/laravel-console-menu API (MIT).
- **Panel layout** — `Widgets\Panel` + `Widgets\PanelBlock` (`Tools\Contracts\Renderable`):
  a multi-column / nestable layout engine, ported and hardened from
  `ajaxray/ansikit` (MIT). `Console::panel()`.
- `Support\Keypress` (`Console::keypress()`) — raw key/arrow/modifier reader,
  POSIX-guarded with pure mappers; and `Support\Terminal` (`Console::terminal()`) —
  bell, tab title, alt-screen, cursor/erase. Ported/expanded from `ajaxray/ansikit`.
- `THIRD_PARTY.md` crediting ajaxray/ansikit, nunomaduro/laravel-console-menu,
  bramus/ansi-php and symfony/tui.
- **Banner designer** — `Banner` gains `font()` (FIGlet big-text via the new
  `Support\Figlet` `.flf`/bundled-font renderer), `align()`, `color()`/`gradient()`,
  `border()` and `padding()`, with a plain-title fallback when a font is missing or
  too wide. Ships the bundled `block` font (`resources/fonts/`), configurable via
  `config('console.banner.*')`.
- ANSI primitives `Support\Sgr` (SGR styles + granular per-attribute resets),
  `Support\ControlChars` (the full C0 set) and `Support\Csi` (typed CSI builder),
  re-derived from ECMA-48.
- `Support\Emoji` — a fluent ASCII/Unicode emoji helper (auto/unicode/ascii mode,
  `:shortcode:` interpolation, custom maps, strip), exposed as `Console::emoji()`
  and configurable via `config('console.emoji.*')`.
- `Widgets\Summary` — an execution-summary widget (statistics, performance
  metrics, error details, status badges) rendered from a stats array.
- `Widgets\Header` — a glyph-prefixed section header with an optional item count.
- `Support\FileSize::format()` — human-readable byte sizes (single source of truth).
- `Support\TimeFormat::fromMillis()` — millisecond-scale adaptive time formatting.
- `TaskProgress` rows now show a live **ETA** (`Task::eta()`), estimated from
  elapsed progress.

### Changed

- `ConsoleUIFormatter` is slimmed to single-string primitives (colour, style,
  badge, link, `colorize`, `sanitizeText`); composite/multi-line UI is the
  widgets' job now.
- `CommandDisplayService` renders through the widget layer (`StatusLine`/`Table`/
  `ProgressBar`) instead of re-implementing output.
- `Support\Symbols` is the single tree/status glyph source (absorbed the
  formatter's `TREE_SYMBOLS`).

### Moved / removed

- Removed from `ConsoleUIFormatter` (use the widgets/Support instead):
  `statusLine()`/`statusLineWithBadge()` → `Widgets\StatusLine`; `header()` →
  `Widgets\Header`; `displaySummary()`/`displayStatisticsTable()`/
  `displayPerformanceMetrics()`/`displayErrorDetails()`/`getExecutionStatusBadges()`/
  `statisticsLine()` → `Widgets\Summary`; `progress()`/`progressBadge()` →
  `Widgets\ProgressBar`/`StatusLine`; `getTreeSymbol()`/`treeLine()`/`TREE_SYMBOLS`
  → `Support\Symbols`; `formatRuntime()`/`getPerformanceColor()` →
  `Support\TimeFormat`/`Widgets\Summary`; `formatClassName()`/`getShortClassName()`
  → `class_basename()`.

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
