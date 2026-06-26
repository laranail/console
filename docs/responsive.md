# Responsive output

Widgets adapt to the terminal width so content never overflows a narrow terminal.

## How it works

- **On by default.** `config('console.responsive')` (default `true`) toggles it
  globally. Width is resolved by [`Capabilities::width()`](tools/support.md)
  (config → `COLUMNS` → Symfony Terminal → 80).
- **Explicit width wins.** A widget's `->width($n)` always takes precedence.
- **Per-widget opt-out.** `->responsive(false)` disables clamping for one widget.
- **No surprises.** Clamping only changes output that *would have overflowed*;
  content that already fits renders unchanged.

```php
// fits the terminal automatically
echo Console::box([$veryLongLine])->render();

// fixed width regardless of terminal
echo Console::box(['x'])->width(30)->render();

// keep legacy (unclamped) behaviour for one call
echo Console::table()->headers($h)->rows($r)->responsive(false)->render();
```

## Per-widget behaviour

| Widget | Behaviour when too wide |
|--------|-------------------------|
| `Box` / `Callout` / `Banner` | frame clamps to terminal; long lines truncated |
| `Table` | column widths shrink/wrap to fit — **columns are never dropped** |
| `Paragraph` / typography | word-wrap to the available width |
| `Heading` / `KeyValue` / `Tree` | rows clipped to the terminal width |
| `BarChart` / `AnimatedBar` | bars scale to the available width |
| `Summary` | divider clamps to the terminal |

The whole [design-system](design-system.md) layer (typography, documents) is
responsive throughout.

## Testing at a fixed width

```php
use Simtabi\Laranail\Console\Testing\InteractsWithConsole;

$this->withConsoleCapabilities(width: 40, unicode: false);
```

See [Testing](tools/testing.md).

[← Docs index](../README.md#documentation)
