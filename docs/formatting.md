# Formatting

Two classes under `Simtabi\Laranail\ConsoleTools\Formatting`.

## ConsoleUIFormatter

A fluent + static helper for colourised Symfony Console output: colors,
background colors, text styles, badges, status lines, tree symbols,
statistics lines and headers. Terminal color/Unicode support is
auto-detected.

```php
use Simtabi\Laranail\ConsoleTools\Formatting\ConsoleUIFormatter;

// Static one-liners
echo ConsoleUIFormatter::success('Done!');
echo ConsoleUIFormatter::error('Failed');
echo ConsoleUIFormatter::warning('Careful');
echo ConsoleUIFormatter::info('Note');

// Status line: label, status, duration, isLast
echo ConsoleUIFormatter::statusLine('BuildAssets', 'DONE', '12.30', true);

// Fluent builder
echo ConsoleUIFormatter::create()
    ->addMessage('Processing')
    ->addTextColor(ConsoleUIFormatter::GREEN)
    ->addTextStyles(ConsoleUIFormatter::BOLD)
    ->render();

// Badges
echo ConsoleUIFormatter::badge('NEW', ConsoleUIFormatter::BADGE_STYLE_SUCCESS);
```

Color/style/badge constants (`GREEN`, `RED`, `BOLD`, `BADGE_STYLE_*`, …)
and `TREE_SYMBOLS`/`ANSI_COLORS` maps are public on the class.

## ConsoleProgressBar

A wrapper around Symfony's `ProgressBar` that integrates `ConsoleUIFormatter`
styling and tracks memory. Call `startProgressBar()` before advancing.

```php
use Simtabi\Laranail\ConsoleTools\Formatting\ConsoleProgressBar;

$bar = (new ConsoleProgressBar)->startProgressBar('Importing', $rows);
foreach ($rows as $row) {
    // ... work ...
    $bar->advanceProgressBar();
}
$bar->finishProgressBar('Imported');
```

---

[← Docs index](../README.md#documentation)
