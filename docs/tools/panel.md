# Panel layout

`Panel` and `PanelBlock` (`Simtabi\Laranail\Console\Tools\Widgets`,
`Console::panel()`) compose a **multi-column / nestable layout** — the piece a
single-widget toolkit usually lacks. Both implement `Tools\Contracts\Renderable`,
so panels nest inside panels.

Widths are display-width aware (multibyte/ANSI safe), borders use
[`BorderStyle`](support.md#borderstyle) with an ASCII fallback, and side-by-side
layouts shrink to fit the terminal.

## PanelBlock

```php
use Simtabi\Laranail\Console\Tools\Widgets\PanelBlock;

echo PanelBlock::make("Name: ada\nRole: eng")->border()->render();
echo PanelBlock::make($longText)->width(30)->wrap()->render(); // word-wrap to 30 cols
```

| Method | Effect |
|--------|--------|
| `content(string\|list<string>)` | block text |
| `width(int)` / `height(int)` | fixed total size (content is padded/truncated) |
| `wrap(bool=true)` | greedy word-wrap to the fixed width |
| `border(bool=true)` | draw a border |
| `style(BorderStyle)` | border family |
| `render()` / `renderLines()` / `totalWidth()` / `totalHeight()` | output / metrics |

## Panel

```php
use Simtabi\Laranail\Console\Tools\Widgets\Panel;
use Simtabi\Laranail\Console\Tools\Widgets\PanelBlock;

echo Panel::make()->horizontal()->dividers()
    ->add(PanelBlock::make("CPU\n42%"))
    ->add(PanelBlock::make("MEM\n7.1G"))
    ->add(PanelBlock::make("NET\n1.2M"))
    ->render();

echo Panel::make()->border()->dividers()
    ->add(PanelBlock::make('Header'))
    ->add(Panel::make()->horizontal()->add(PanelBlock::make('A'))->add(PanelBlock::make('B')))
    ->render(); // panels nest
```

| Method | Effect |
|--------|--------|
| `add(Renderable)` | append a block or nested panel |
| `vertical()` / `horizontal()` | stack rows, or columns side-by-side (default vertical) |
| `dividers(bool=true)` | lines between blocks |
| `border(bool=true)` | outer frame |
| `sizes(array<int,int>)` | per-column widths (horizontal) |
| `style(BorderStyle)` | border family |
| `render(?OutputInterface)` | string (and optionally write to output) |

[← Docs index](../../README.md#documentation)
