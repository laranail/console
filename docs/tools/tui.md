# Full-screen TUI (symfony/tui)

The package integrates [`symfony/tui`](https://github.com/symfony/tui) — Symfony's
**experimental** full-screen terminal-UI framework (widget tree, focus,
keybindings, CSS-like stylesheet, event-loop renderer) — so you can build
interactive, full-screen apps and **mount our output widgets inside them**.

> **Requirements:** symfony/tui needs **PHP ≥ 8.4.1**, Symfony 8 components and
> `revolt/event-loop`, and is shipped at `minimum-stability: dev`. It is a hard
> dependency of this package, so the package itself requires PHP ≥ 8.4.1. The
> component is **experimental** — its API may change between releases.

## Mounting our widgets

`Console\Tui\RenderableWidget::of()` adapts any of our widgets — a
`Tools\Contracts\Renderable` (Panel/PanelBlock), any `Stringable` widget (Box,
Table, Tree, Gauge, Sparkline, Banner, Summary, Header, Columns…), or a raw
string — into a `symfony/tui` widget.

```php
use Simtabi\Laranail\Console\Facades\Console;
use Simtabi\Laranail\Console\Tui\RenderableWidget;

$tui = Console::tui();                 // a ready Symfony\Component\Tui\Tui app

$tui->add(RenderableWidget::of(
    Console::box(['Welcome'])->title('app')->rounded()
));
$tui->add(RenderableWidget::of(
    Console::table()->fromAssoc([['name' => 'ada', 'role' => 'eng']])
));

$tui->run();                           // full-screen event loop (Ctrl-C / q to quit)
```

`RenderableWidget::of($widget)->toLines()` returns the wrapped widget's lines and
is pure (no event loop), which is handy in tests.

## When to use it

- **Incremental output** (a command that prints a spinner, table, then exits):
  use the [widgets](widgets.md) directly — no event loop needed.
- **Full-screen, interactive, redrawing UI** (dashboards, editors, pickers): use
  `Console::tui()` + `RenderableWidget`, plus symfony/tui's own widgets
  (`TextWidget`, `InputWidget`, `SelectListWidget`, `EditorWidget`, …).

See the runnable demo in `examples/tools/tui.php` (interactive; not part of the CI
smoke set). Attribution: [THIRD_PARTY.md](../../THIRD_PARTY.md).

[← Docs index](../../README.md#documentation)
