# Support utilities

The `Simtabi\Laranail\Console\Tools\Support` namespace is the shared backbone every
widget builds on. It's public, so you can use it directly when composing your own
output.

## Capabilities

Detects the terminal's abilities, honouring the package config and the de-facto
environment standards.

```php
use Simtabi\Laranail\Console\Tools\Support\Capabilities;

$caps = Capabilities::detect();

$caps->isInteractive();       // bool — stdout is a TTY
$caps->supportsColor();       // bool
$caps->supportsTrueColor();   // bool — 24-bit
$caps->supports256Color();    // bool — xterm-256
$caps->supportsUnicode();     // bool
$caps->width(80);             // int  — columns (config → COLUMNS → Terminal → default)
$caps->symbolMode();          // 'fancy' | 'ascii'
```

Colour-detection precedence: `output.colors` config (`always`/`never`) →
`NO_COLOR` (any non-empty value disables) → `FORCE_COLOR` → `TERM=dumb` → whether
stdout is a TTY. Unicode follows `output.unicode`, then `WT_SESSION`/`ConEmuANSI`/
`TERM_PROGRAM=vscode`, then a UTF-8 locale. The whole package — formatter, status
lines and `Color` — routes through this one detector. See
[Configuration](../configuration.md) to override any of it.

## Color

24-bit / hex foreground colouring and per-character gradients, emitted as **raw
ANSI** (so the returned string is echo-safe — unlike the formatter's Symfony
markup). Colour is gated by `Capabilities::supportsColor()`, degrading
truecolor → xterm-256 → nearest ANSI-16, and is suppressed entirely (plain text
returned) when colour is off, so `NO_COLOR` and pipes stay clean.

```php
use Simtabi\Laranail\Console\Tools\Support\Color;

$c = Color::make();
echo $c->fg('Brand', '#ff8800');                 // 6-digit hex only
echo $c->gradient('Welcome', ['#00ffff', '#ff00ff']); // ≥ 2 stops

Color::isValidHex('#ff8800'); // true (3-digit shorthand is not accepted)
Color::hexToRgb('#ff8800');   // [255, 136, 0]
```

> Because `Color` decides on/off from `Capabilities` (not from a specific
> output's `isDecorated()`), prefer writing its output to a normal/auto-decorated
> output. If you force a non-decorated output on a colour-capable TTY, gate the
> colour yourself.

## DisplayWidth

Visible width that ignores ANSI escapes and accounts for wide characters — use it
(never `strlen`) for any aligned output.

```php
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;

DisplayWidth::of("\033[31mhello\033[0m"); // 5
DisplayWidth::pad('hi', 5);               // 'hi   '
DisplayWidth::padLeft('hi', 5);           // '   hi'
DisplayWidth::center('hi', 6);            // '  hi  '
```

## Symbols

One Unicode↔ASCII glyph map (status icons + tree connectors), chosen once from
`Capabilities::symbolMode()`.

```php
use Simtabi\Laranail\Console\Tools\Support\Symbols;

Symbols::for(Capabilities::detect())->get('success'); // '✓' or '[OK]'
Symbols::fancy()->get('branch');                      // '├─'
Symbols::ascii()->get('branch');                      // '|-'
```

## BorderStyle

The box-drawing families used by `Box::style()` and `Rule::style()`:

```php
use Simtabi\Laranail\Console\Tools\Support\BorderStyle;

BorderStyle::Ascii;   // + - |
BorderStyle::Light;   // ┌ ─ │
BorderStyle::Heavy;   // ┏ ━ ┃
BorderStyle::Rounded; // ╭ ─ │
BorderStyle::Double;  // ╔ ═ ║

Box::make($lines)->style(BorderStyle::Double)->render();
```

Draw a box from a single family — `BorderStyle::Ascii` is the automatic fallback
without Unicode.

## TimeFormat

Three-tier duration formatting used by progress bars and task rows.

```php
use Simtabi\Laranail\Console\Tools\Support\TimeFormat;

TimeFormat::duration(45.2);  // '45.2s'
TimeFormat::duration(138);   // '2m 18s'
TimeFormat::duration(4080);  // '1h 8m'
```

[← Docs index](../../README.md#documentation)
