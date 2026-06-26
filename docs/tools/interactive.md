# Interactive & live output

A dynamic layer for animated and interactive CLIs. **TTY-guarded**: in a
non-interactive terminal (pipe/CI) live/animated/input components render a single
static frame with no cursor-control sequences. Side-effecting components implement
`Contracts\Interactive` (vs pure `Renderable`).

## Live region

[`Support\Live`](../../src/Console/Tools/Support/Live.php) redraws any renderable in
place via a Symfony console section:

```php
Console::live()->refresh(
    fn (int $i) => Console::animatedBar()->label('Build')->fraction($i / 20),
    21,        // steps
    60,        // ms between frames
);
```

`draw($renderable)` writes once; `animate($frames, $loops, $ms)` cycles frames.
Piped/CI → only the final frame is written, once.

## Animated bar

[`AnimatedBar`](../../src/Console/Tools/Widgets/AnimatedBar.php) — determinate
(`->fraction(0..1)`) or indeterminate (`->indeterminate($tick)`); responsive +
themed. Drive it with `Live`.

## Badges, pills, buttons

```php
echo Console::badge('OK', 'success')->render();   // filled status chip
echo Console::pill('beta', 'info')->render();      // rounded variant
echo Console::button('Deploy')->focused()->render(); // visual affordance
```

`Button` is a static affordance. For an **interactive choice** use `ButtonGroup`,
which delegates to laravel/prompts `select()` — arrow-key navigation on a TTY, a
graceful non-interactive fallback, and fully fakeable in tests:

```php
$choice = Console::buttonGroup(['deploy' => 'Deploy', 'cancel' => 'Cancel'])
    ->prompt('Action');
```

## Building interactive apps

- **Line-based**: `Console::prompter()` (text/confirm/select/multiselect/form),
  `Console::menu()`, `ButtonGroup`, and `Live`.
- **Full-screen** (focus, event loop): the optional
  [`symfony/tui`](tui.md) integration — mount any widget with
  `Console\Tui\RenderableWidget::of()`. We don't reinvent an event loop.

[← Docs index](../../README.md#documentation)
