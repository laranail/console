# Formatting

`ConsoleUIFormatter` (under `Simtabi\Laranail\Console\Tools\Formatting`) is a fluent
+ static helper for colourised Symfony Console output: colours, backgrounds, text
styles, badges, status lines, tree symbols, links, and a small reporting toolkit.
Colour/Unicode support is auto-detected via the shared
[Capabilities](support.md#capabilities).

## Two output modes

- **Markup** — `success()`, `error()`, `warning()`, `info()`, `format()`,
  `badge()`, `render()` return Symfony Console markup (`<fg=green>…</>`). Write
  them **through an output** so colour renders (echoing prints literal tags):

  ```php
  use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

  $output->writeln(ConsoleUIFormatter::success('Done!'));
  $output->writeln(ConsoleUIFormatter::badge('NEW', ConsoleUIFormatter::BADGE_STYLE_SUCCESS));

  $output->writeln(
      ConsoleUIFormatter::create()
          ->addMessage('Processing')
          ->addTextColor(ConsoleUIFormatter::GREEN)
          ->addTextStyles(ConsoleUIFormatter::BOLD)
          ->render()
  );
  ```

- **Raw ANSI (echo-safe)** — `colorize()`, and the `statusLine()`/`header()`/
  `statisticsLine()` reporting helpers, emit ready-to-print strings:

  ```php
  echo ConsoleUIFormatter::create()->colorize('OK', ConsoleUIFormatter::GREEN, bold: true);
  echo ConsoleUIFormatter::statusLine('BuildAssets', 'DONE', '12.30', isLast: true);
  ```

## Reporting toolkit

Static helpers for execution summaries (all echo-safe):

```php
echo ConsoleUIFormatter::displaySummary($stats);            // full summary block
echo ConsoleUIFormatter::displayStatisticsTable([...]);     // label/value/badge rows
echo ConsoleUIFormatter::displayPerformanceMetrics($stats); // timings + success rate
echo ConsoleUIFormatter::header('Modules', 12, 'items');
echo ConsoleUIFormatter::link('https://example.com', 'Docs'); // OSC-8, scheme allow-listed
```

Colour/style/badge constants (`GREEN`, `RED`, `BOLD`, `BADGE_STYLE_*`, …) and the
`TREE_SYMBOLS`/`ANSI_COLORS` maps are public on the class.

## Progress bars

For progress, use the flavoured [`ProgressBar` widget](widgets.md#progress-bar)
(`Console::progress()`) — percent/ETA/rate, glyph styles, and instance-scoped
placeholders.

[← Docs index](../../README.md#documentation)
