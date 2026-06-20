# Colours & styles

`Console::color()` ([`Support\Color`](../../src/Console/Tools/Support/Color.php)) and
`Console::style()` ([`Support\Style`](../../src/Console/Tools/Support/Style.php)) produce
ANSI-coloured strings that **degrade gracefully**: 24-bit truecolor → xterm-256 →
nearest ANSI-16 → plain (under `NO_COLOR` / non-TTY), decided by
[`Capabilities`](support.md).

## Colour specs

`Color` accepts any of these wherever a colour is expected (also in themes/banners):

| Form | Example |
|------|---------|
| hex | `#7c3aed`, `7c3aed` |
| `rgb()` | `rgb(124, 58, 237)` |
| `hsl()` | `hsl(262, 83%, 58%)` |
| named | `red`, `orange`, `slate`, `crimson` |
| xterm-256 index | `@196` |

```php
$c = Console::color();
echo $c->fg('Hello', '#7c3aed');      // foreground (downgrades automatically)
echo $c->bg('Hello', 'slate');        // background
echo $c->gradient('rainbow', ['#f00', '#0f0', '#00f']);

Color::blend('#ff0000', '#0000ff', 0.5);   // '#800080'
Color::adaptive('#000000', '#ffffff');     // picks by terminal background (COLORFGBG)
```

## Fluent style

`Style` is an immutable, chainable value object — each setter returns a new instance:

```php
echo Console::style()->fg('#16a34a')->bold()->underline()->apply('Saved');
```

Attributes: `fg() bg() bold() dim() italic() underline() strikethrough() inverse()
blink()`. Empty styles and unsupported colour are no-ops (text returned unchanged).

For inline composition with emoji/symbols and theme roles, use
[`Console::text()`](typography.md#text).

[← Docs index](../../README.md#documentation)
