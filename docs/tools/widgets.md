# Output widgets

All widgets live under `Simtabi\Laranail\Console\Tools\Widgets` and most have a
`Console::…` facade accessor. Each consumes the shared
[Support backbone](support.md), so Unicode/colour degrade to ASCII/plain
automatically. A runnable demo is at `examples/tools/widgets.php`.

**Two output shapes** (see the README's "Writing through an output"):

- **Self-writing** — `Spinner`, `ProgressBar`, `TaskProgress` write to an
  `OutputInterface` you give them (no `render()`).
- **String-returning** — every other widget has `render(): string` returning a
  finished, echo-safe string.

> Most widgets have a facade accessor (`Console::box()`, `Console::tree()`, …).
> `Callout` has **no** accessor — use the class directly (`Callout::warning(...)`).

## Signature reference

| Widget | Facade | Key fluent methods |
|--------|--------|--------------------|
| `Spinner` | `Console::spinner($msg)` | `frames(SpinnerFrames\|string)`, `run(callable)`, `start()`, `advance()`, `finish($status='success', ?$msg=null)` |
| `ProgressBar` | `Console::progress($output, $max)` | `format(ProgressStyle\|string)`, `glyphs(string)`, `start(?$max)`, `advance($n=1)`, `setProgress($n)`, `finish()`, `raw()` |
| `TaskProgress` | `Console::tasks($output)` | `task($name, $total=0): Task`, `draw()`, `finish(): int`, `exitCode(): int` |
| `Task` | — | `start()`, `advance($n=1)`, `succeed($note='')`, `fail($note='')`, `skip($note='')`, `warn($note='')`, `elapsed()`, `percent()`, `eta()` |
| `StatusLine` | `Console::status()` | `success/error/warning/info/pending($msg)`, `line($status, $msg)` → **markup string** |
| `Rule` | `Console::rule($title)` | `style(BorderStyle)`, `width($n)`, `center()`, `render()` |
| `Box` | `Console::box($content)` | `title()`, `footer()`, `content()`, `padding($n)`, `width($n)`, `style(BorderStyle)`, `rounded()`/`double()`/`heavy()`, `render()` |
| `Tree` | `Console::tree($label)` | `child($label, ?callable)`, `status($status)`, `render()` |
| `Table` | `Console::table()` | `headers()`, `rows()`, `grouped($groups)`, `tree($rows)`, `style($preset)`, `render(?$output)` |
| `Callout` | — (class) | `Callout::success/error/warning/info($msg)`, `title()`, `render()` |
| `Banner` | `Console::banner($title)` | `subtitle()`, `boxed($bool=true)`, `width($n)`, `render()` |
| `Gauge` | `Console::gauge($value, $max=100)` | `label()`, `width($barWidth)`, `showValue($bool=true)`, `render()` |
| `Sparkline` | `Console::sparkline($values)` | `render()` (numeric-summary fallback without Unicode) |
| `StepFlow` | `Console::steps($steps)` | `step($label)`, `current($index)`, `render()` |
| `Summary` | — (class) | `Summary::make($stats, $title='EXECUTION SUMMARY')`, `render()` → **raw ANSI** |
| `Header` | — (class) | `Header::make($title)`, `count($n, $label='items')`, `render()` → **raw ANSI** |

## Spinner

```php
Console::spinner('Compiling…')->run(fn () => compile());      // callback mode
$s = Console::spinner('Working')->frames('dots')->start();    // manual mode
$s->advance(); /* … */ $s->finish('success', 'Done');
```

Frame sets: `braille` (default), `dots`, `line`, `breath`. `run()` delegates
animation to Laravel Prompts (pcntl/non-TTY safe) and uses its own frames — a
custom `frames()` applies to the manual `start()/advance()/finish()` mode.

## Progress bar

```php
$bar = Console::progress($output, max: count($items))
    ->format('detailed')   // minimal | classic | detailed
    ->glyphs('blocks');    // blocks | ascii | dots | arrows | gradient
$bar->start();
foreach ($items as $item) { /* … */ $bar->advance(); }
$bar->finish();
```

`detailed` shows percent, count, elapsed, **ETA** and **rate** with three-tier
time formatting. Placeholders are registered on the instance only — they never
affect other Symfony progress bars. `raw()` exposes the underlying Symfony bar.

## Multi-task progress

```php
$tasks = Console::tasks($output);
$compile = $tasks->task('Compile', steps: 100);
$compile->advance(100)->succeed();
$tasks->task('Upload')->fail('network error');
exit($tasks->finish()); // non-zero if any task failed
```

Each row shows the status glyph, name, count, percent, **elapsed and a live ETA**
(`Task::eta()`, estimated from elapsed progress; `null` until there's progress,
`0.0` once finished). Redraws in place on a TTY (`ConsoleSectionOutput`); on a
non-TTY it emits one line per state change (start / warn / terminal) so CI logs
stay readable.

## Summary & header

```php
echo Header::make('Modules')->count(12, 'items')->render();   // 📦 Modules (12 items)
echo Summary::make([
    'total' => 3, 'success' => 2, 'failed' => 1,
    'totalTime' => 1234.0,                       // milliseconds
    'fastest' => ['class' => 'Fast', 'time' => 10.0],
    'slowest' => ['class' => 'Slow', 'time' => 900.0],
    'errors'  => [['class' => 'Boom', 'type' => 'RuntimeException', 'message' => '…']],
])->render();
```

`Summary` renders an execution report (statistics, performance metrics, error
details, status badges) from a stats array; `Header` a glyph-prefixed section
title with an optional item count. Both return echo-safe raw ANSI. (These were
previously static helpers on `ConsoleUIFormatter`; they now live here as widgets.)

## Status, rule, box, tree

```php
$output->writeln(Console::status()->success('Done')); // markup → write through output
echo Console::rule('SECTION')->width(60)->render();
echo Console::box(['line one', 'line two'])->title('Config')->footer('ok')->rounded()->render();
echo Console::tree('app')->child('Console', fn ($t) => $t->child('Tools'))->child('Providers')->render();
```

A fixed `Box`/`Rule` `width()` is a minimum — content never overflows the frame.

## Tables, callouts, banners

```php
echo Console::table()->headers(['Name', 'Status'])->rows([['web', 'up']])->style('light')->render();
echo Console::table()->headers(['Svc', 'St'])->grouped(['Web' => [['nginx', 'up']]])->render();
echo Console::table()->headers(['Path'])->tree([[0, ['app']], [1, ['Console']]])->render();
echo Callout::warning('Disk almost full')->title('Heads up')->render();
echo Console::banner('app v1.0')->subtitle('Simtabi')->boxed()->render();
```

Table styles: `ascii`, `light`, `double`, `compact`, `borderless`, `markdown`.

## Gauges, sparklines, step flow

```php
echo Console::gauge(184, 250)->label('Disk')->showValue()->render(); // [██████░░] 74% (184/250)
echo Console::sparkline([1, 3, 2, 5, 7, 6, 4])->render();            // ▁▃▂▅█▆▄
echo Console::steps(['Detect', 'Plan', 'Apply'])->current(1)->render(); // ✓ Detect → ● Plan → ○ Apply
```

## Colour

```php
$color = Console::color();
echo $color->fg('Brand', '#ff8800');                  // raw ANSI (echo-safe)
echo $color->gradient('Welcome', ['#00ffff', '#ff00ff']);
```

See [Support utilities](support.md#color) for the colour-depth degradation rules.

[← Docs index](../../README.md#documentation)
