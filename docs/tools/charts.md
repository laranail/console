# Charts

A family of labelled, responsive, themed terminal charts. All are fluent
(`make()` → setters → `render()`), implement `Renderable` (so they nest in panels
and documents), and degrade gracefully — ASCII glyphs without Unicode, plain/shade
output without colour.

| Chart | Facade | Best for |
|-------|--------|----------|
| `BarChart` | `Console::barChart([label => value])` | horizontal magnitude comparison |
| `ColumnChart` | `Console::columnChart([label => value])` | vertical magnitude comparison |
| `LineChart` | `Console::lineChart($series)` | trends / time-series (one or many series) |
| `ScatterPlot` | `Console::scatterPlot([[x, y], …])` | correlation / point clouds |
| `Heatmap` | `Console::heatmap($matrix)` | 2D intensity / activity maps |
| `Histogram` | `Console::histogram($values)` | value distributions (binned) |

See also the inline [`Sparkline`](widgets.md) and [`Gauge`](widgets.md) for compact,
single-line readouts.

## Bar chart (horizontal)

```php
echo Console::barChart(['API' => 1240, 'Web' => 860, 'CLI' => 320])->render();
```

Bars scale to the largest value and the available width. Setters: `add($label, $value)`,
`width($n)`, `responsive($bool)`, `showValues($bool)`. Glyphs degrade `█/░` → `#/-`
without Unicode; colour follows the theme `primary` role.

## Column chart (vertical)

```php
echo Console::columnChart(['Mon' => 12, 'Tue' => 19, 'Wed' => 7])->height(6)->render();
```

Vertical bars using block-eighths (`▁▂▃▄▅▆▇█`) for sub-row precision. Setters:
`add()`, `height($rows)`, `width($n)`, `responsive($bool)`. Columns shrink to fit;
degrades to `#` columns without Unicode.

## Line chart

```php
echo Console::lineChart([1, 5, 2, 8, 3, 9])->height(6)->render();          // single series
echo Console::lineChart(['api' => [..], 'web' => [..]])->render();          // multiple
```

Plots into a [`BrailleCanvas`](support.md) at 2×4 sub-cell resolution with a
min/max y-axis gutter. Each series gets its own theme colour (one colour per cell,
last writer wins on overlap). Setters: `series($name, $values)`, `height($rows)`,
`width($n)`, `responsive($bool)`. Without Unicode it falls back to an ASCII plot.

## Scatter plot

```php
echo Console::scatterPlot([[1, 2], [3, 5], [6, 3], [8, 9]])->height(6)->render();
```

`(x, y)` points placed into a `BrailleCanvas`, auto-scaled to fit, with a y-axis
gutter. Setters: `add($x, $y)`, `height()`, `width()`, `responsive()`.

## Heatmap

```php
echo Console::heatmap([[1, 2, 3], [4, 5, 6]])->labels(['r0', 'r1'], ['c0', 'c1', 'c2'])->render();
```

A 2D matrix coloured by intensity (a low→high blend; truecolor→256→16 downgrade is
handled by `Style`). Without colour it falls back to a Unicode shade ramp (`░▒▓█`)
or an ASCII ramp. Setters: `labels($rows, $cols)`, `cellWidth($n)`, `width($n)`,
`responsive($bool)`. Cells shrink to keep the grid within the terminal — columns are
never dropped.

## Histogram

```php
echo Console::histogram([1, 2, 2, 3, 3, 3, 4, 5])->bins(5)->height(6)->render();
```

Bins raw values into a frequency distribution and renders it as a column chart. The
bin count defaults to Sturges' rule (`⌈log2 n⌉ + 1`); override with `bins($n)`.
Setters: `bins()`, `height()`, `width()`, `responsive()`.

[← Docs index](../../README.md#documentation)
