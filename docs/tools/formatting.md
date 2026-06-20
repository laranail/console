# Formatting

`ConsoleUIFormatter` (under `Simtabi\Laranail\Console\Tools\Formatting`) is the
low-level **text-formatting primitive**: colours, backgrounds, text styles,
badges and links for a single string. Colour/Unicode support is auto-detected via
the shared [Capabilities](support.md#capabilities).

> Composite, multi-line UI components are **widgets**, not formatter methods.
> Status lines → [`StatusLine`](widgets.md#status-rule-box-tree); execution
> reports → [`Summary`](widgets.md#summary--header); section titles →
> [`Header`](widgets.md#summary--header); trees → [`Tree`](widgets.md); progress →
> [`ProgressBar`](widgets.md#progress-bar); tree glyphs →
> [`Symbols`](support.md#symbols). The formatter keeps only single-string primitives.

## Two output modes

- **Markup** — `success()`, `error()`, `warning()`, `info()`, `format()`,
  `badge()`, `badges()`, `link()`, `hex()` return Symfony Console markup
  (`<fg=green>…</>`). Write them **through an output** so colour renders
  (echoing prints literal tags). (`render()` is an *instance* method on a
  configured formatter, not one of these static helpers.)

  ```php
  use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

  $output->writeln(ConsoleUIFormatter::success('Done!'));
  $output->writeln(ConsoleUIFormatter::badge('NEW', ConsoleUIFormatter::BADGE_STYLE_SUCCESS));
  $output->writeln(ConsoleUIFormatter::link('Docs', 'https://example.com')); // OSC-8, scheme allow-listed

  $output->writeln(
      ConsoleUIFormatter::create()
          ->addMessage('Processing')
          ->addTextColor(ConsoleUIFormatter::GREEN)
          ->addTextStyles(ConsoleUIFormatter::BOLD)
          ->render()
  );
  ```

- **Raw ANSI (echo-safe)** — `colorize()` emits a ready-to-print coloured string:

  ```php
  echo ConsoleUIFormatter::create()->colorize('OK', ConsoleUIFormatter::GREEN, bold: true);
  ```

Colour/style/badge constants (`GREEN`, `RED`, `BOLD`, `BADGE_STYLE_*`, …) and the
`ANSI_COLORS` map are public on the class. `getBadgeStyles()` lists the badge
styles. All input is sanitised of terminal control characters via
`sanitizeText()`.

## Progress, reports, status lines

These moved to the widget layer:

- Execution summaries → `Summary::make($stats)->render()` ([widgets](widgets.md#summary--header)).
- Section headers → `Header::make($title)->count($n)->render()`.
- Status lines → `Console::status()->success(...)` ([`StatusLine`](widgets.md)).
- Progress → the flavoured [`ProgressBar` widget](widgets.md#progress-bar)
  (`Console::progress()`) — percent/ETA/rate, glyph styles, instance-scoped
  placeholders.

[← Docs index](../../README.md#documentation)
