# Full-screen TUI (symfony/tui)

The package integrates [`symfony/tui`](https://github.com/symfony/tui) — Symfony's
**experimental** full-screen terminal-UI framework (widget tree, focus,
keybindings, CSS-like stylesheet, event-loop renderer) — so you can build
interactive, full-screen apps and **mount our output widgets inside them**.

> **Optional dependency.** `symfony/tui` is *not* required by the package — install
> it to enable this feature:
>
> ```bash
> composer require symfony/tui
> ```
>
> It needs **PHP ≥ 8.4.1** (already this package's floor), Symfony 8 and
> `revolt/event-loop`, and ships at `minimum-stability: dev`, so your app's
> `composer.json` needs `"minimum-stability": "dev"` + `"prefer-stable": true`.
> The component is **experimental** — its API may change. Without it,
> `Console::tui()` throws a clear `ConsoleException`; the rest of the toolkit is
> unaffected and installs stably.

> **Not covered by the 1.0 BC guarantee.** Because this integration rides the
> upstream `symfony/tui` `@dev` package, `Console\Tui\*` and `Console::tui()` are
> **excluded from SemVer** — they may change or break with the upstream at any time
> (see [Versioning & stability](../release.md#versioning--stability)). The rest of
> the package is stable.

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
smoke set).

[← Docs index](../../README.md#documentation)
