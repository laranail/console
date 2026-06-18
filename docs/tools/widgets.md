# Output widgets

All widgets live under `Simtabi\Laranail\Console\Tools\Widgets` and are reachable
from the `Console` facade. Each is fluent and has a `render(): string` (the
status/formatter-backed ones return Symfony markup, so write them through a
console output to get colour). A runnable demo lives at
`examples/tools/widgets.php`.

Every widget consumes the shared `Tools\Support` backbone — terminal capability
detection, display-width-correct alignment, a single glyph map and colour with
graceful degradation — so Unicode/colour fall back to ASCII/plain automatically.

## Spinner

```php
Console::spinner('Compiling…')->run(fn () => compile());      // callback mode
$s = Console::spinner('Working')->frames('dots')->start();    // manual mode
$s->advance(); /* … */ $s->finish('success', 'Done');
```

Frame sets: `braille` (default), `dots`, `line`, `breath`. Animation in `run()`
is delegated to Laravel Prompts (pcntl/non-TTY safe).

## Progress bar

```php
$bar = Console::progress($output, max: count($items))
    ->format('detailed')   // minimal | classic | detailed
    ->glyphs('blocks');    // blocks | ascii | dots | arrows | gradient
$bar->start();
foreach ($items as $item) { /* … */ $bar->advance(); }
$bar->finish();
```

`detailed` shows percent, count, elapsed, **ETA** and **rate**, with three-tier
time formatting (`45.2s` / `2m 18s` / `1h 8m`).

## Multi-task progress

```php
$tasks = Console::tasks($output);
$compile = $tasks->task('Compile', steps: 100);
$compile->advance(100)->succeed();
$tasks->task('Upload')->fail('network error');
exit($tasks->finish()); // non-zero if any task failed
```

Redraws in place on a TTY (via `ConsoleSectionOutput`), one line per event on a
non-TTY, and the exit code reflects failures — handy for CI.

## Status, rule, box, tree

```php
Console::status()->success('Done');     // also error/warning/info/pending
Console::rule('SECTION')->width(60);
Console::box(['line one', 'line two'])->title('Config')->footer('ok')->rounded();
Console::tree('app')->child('Console', fn ($t) => $t->child('Tools'))->child('Providers');
```

## Tables, callouts, banners

```php
Console::table()->headers(['Name', 'Status'])->rows([['web', 'up']])->style('light');
Callout::warning('Disk almost full')->title('Heads up')->render();
Console::banner('app v1.0')->subtitle('Simtabi')->boxed();
```

Table styles: `ascii`, `light`, `double`, `compact`, `borderless`, `markdown`.

## Gauges, sparklines, step flow

```php
Console::gauge(184, 250)->label('Disk')->showValue();   // [██████░░] 74% (184/250)
Console::sparkline([1, 3, 2, 5, 7, 6, 4]);              // ▁▃▂▅█▆▄
Console::steps(['Detect', 'Plan', 'Apply'])->current(1); // ✓ Detect → ● Plan → ○ Apply
```

## Colour

```php
$color = Console::color();
$color->fg('Brand', '#ff8800');           // truecolor (degrades to 16-colour / plain)
$color->gradient('Welcome', ['#00ffff', '#ff00ff']);
```

[← Docs index](../../README.md#documentation)
