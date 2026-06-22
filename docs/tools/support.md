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
$caps->width();               // int  — columns (config → COLUMNS → Terminal → default)
$caps->symbolMode();          // 'fancy' | 'ascii'
```

Colour-detection precedence: `output.colors` config (`always`/`never`) →
`NO_COLOR` (any non-empty value disables) → `FORCE_COLOR` → `TERM=dumb` → whether
stdout is a TTY. Unicode follows `output.unicode`, then `WT_SESSION`/`ConEmuANSI`/
`TERM_PROGRAM=vscode`, then a UTF-8 locale. The whole package — formatter, status
lines and `Color` — routes through this one detector. See
[Configuration](../configuration.md) to override any of it.

## Os

Platform / environment detection (`Console::os()`) — branch output on the host OS,
WSL or CI.

```php
use Simtabi\Laranail\Console\Tools\Support\Os;

$os = Os::make();
$os->isWindows(); $os->isMacos(); $os->isLinux();  // bool
$os->isWsl();                                        // bool — Linux under WSL
$os->isCi();                                         // bool — CI/GITHUB_ACTIONS/… set
$os->terminalProgram();                              // 'vscode' | 'iTerm.app' | … | null
$os->family();                                       // 'windows' | 'macos' | 'linux' | 'unknown'
```

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

Color::parse('rgb(255,136,0)'); // '#ff8800' — named/hex/rgb()/hsl()/@256 → 6-digit hex, or null
Color::parseStrict('#zzz');     // throws InvalidColorException on an invalid spec
Color::blend('#000000', '#ffffff', 0.5); // '#808080' — mix two colours (t = 0.0–1.0)
Color::adaptive('#111111', '#eeeeee');    // adaptive(light, dark) — pick by terminal background
```

To apply styles (fg/bg + bold/italic/underline…) to text, use the fluent
[`Style`](colors.md) builder (`Console::style()`).

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
DisplayWidth::truncate('hello world', 5); // 'hello' (by display width, wide-char aware)
DisplayWidth::truncateAnsi("\033[31mhello world\033[0m", 5); // truncates to width 5, keeping + closing ANSI
DisplayWidth::maxWidth(['hi', 'hello']);  // 5 — the widest line's display width
```

## Align

Horizontal alignment of a block of lines within a target width — ANSI/wide-char
aware (via `DisplayWidth`). `pad()` stretches each line to the exact width; `place()`
keeps the block at its own width and shifts it with a margin (centre/right a box
without stretching it).

```php
use Simtabi\Laranail\Console\Tools\Support\Align;

Align::pad(['hi', 'hello'], 6, Align::CENTER);  // ['  hi  ', 'hello ']
Align::place(['box'], 9, Align::RIGHT);          // ['      box']
Align::normalize('middle');                       // 'left' (unknown → left)

Align::LEFT; Align::CENTER; Align::RIGHT; Align::JUSTIFY; // the alignment tokens
```

## Emoji

Serves Unicode emoji or an ASCII fallback, decided by mode (`auto` follows
`Capabilities::supportsUnicode()`). Configurable globally via
`config('console.emoji.mode')` / `config('console.emoji.custom')`, and per call.

```php
use Simtabi\Laranail\Console\Tools\Support\Emoji;

Console::emoji()->get('rocket');                    // 🚀  (or '->' in ascii mode)
Console::emoji()->ascii()->get('rocket');           // '->'
Console::emoji()->render('Build :check: in :zap:'); // 'Build ✅ in ⚡'
Console::emoji()->with(['deploy' => ['🚀', '>>']])->get('deploy');
Console::emoji()->strip('Done :tada:');             // 'Done'  (for plain logs)
```

`get($name, $default)` returns `$default` (or `''`) for unknown names; `render()`
leaves unknown `:shortcodes:` untouched; `with()` adds/overrides
(`[unicode, ascii]` or a single string); `has()`/`all()` introspect the set.

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

TimeFormat::duration(45.2);  // '45.2s'   (seconds)
TimeFormat::duration(138);   // '2m 18s'
TimeFormat::duration(4080);  // '1h 8m'
TimeFormat::fromMillis(812); // '812.00 ms' (milliseconds → ms / s / min)
```

## ANSI primitives (Sgr, ControlChars, Csi)

Low-level escape-sequence primitives (re-derived from ECMA-48). They return raw
sequences — gate emission on `Capabilities` at the call site.

```php
use Simtabi\Laranail\Console\Tools\Support\{Sgr, ControlChars, Csi};

Sgr::Underline->open();                  // "\e[4m"
Sgr::sequence(Sgr::Bold, Sgr::Underline);// "\e[1;4m"
Sgr::UnderlineOff->open();               // "\e[24m"  (turn off ONLY underline)
Sgr::wrap('hi', Sgr::Bold);              // "\e[1mhi\e[0m"

ControlChars::Bel->char();               // "\x07"  (bell); full C0 set + DEL
ControlChars::Esc->char();               // "\e"

Csi::sequence('A', 3);                   // "\e[3A"   cursor up 3
Csi::sequence('H', 2, 5);                // "\e[2;5H" cursor to row 2 col 5
```

`Sgr` covers styles plus granular per-attribute resets and rare styles
(framed/encircled/overlined/conceal/blink-rapid). `Terminal` builds its cursor and
erase sequences with `Csi`/`ControlChars`.

## Keypress

Raw key / arrow / modifier reader (`Console::keypress()`). The blocking reads need
a POSIX TTY (`stty`) and are guarded by `isSupported()` — they degrade to `''`/`null`
on Windows or a non-TTY rather than shelling out. The mappers are pure.

```php
use Simtabi\Laranail\Console\Tools\Support\Keypress;

if (Keypress::isSupported()) {
    $key = Console::keypress()->listen();          // blocks; e.g. Keypress::KEY_UP
    $key = Console::keypress()->listenNonBlocking(100); // or null on timeout
}

Keypress::translateKey("\033[A"); // 'UP'
Keypress::getKeyName(Keypress::KEY_UP); // 'UP ARROW'
Keypress::detectAltKey("\033a");  // 'ALT+A'
```

## Terminal

Low-level terminal control (`Console::terminal($output)`), built on `Csi`/
`ControlChars`, written raw through an output.

```php
use Simtabi\Laranail\Console\Tools\Support\Terminal;

Terminal::make($output)
    ->altScreen()->hideCursor()
    ->tabTitle('Deploying…')
    ->moveCursor(1, 1)->clear()
    ->bell();
// …later: ->showCursor()->altScreen(false)->restoreTabTitle();
```

## Figlet

The big-text renderer behind the [Banner designer](banner.md). Loads a bundled
font by name or a standard FIGlet `.flf` file by path, and renders text to a block
of equal-width lines. Unknown glyphs fall back to upper-case then a blank, so it
never throws on content.

```php
use Simtabi\Laranail\Console\Tools\Support\Figlet;

$figlet = Figlet::font('block');     // built-in font (Support\Fonts\BlockFont)
$lines = $figlet->render('HELLO');   // list<string>, $figlet->height() rows
Figlet::builtins();                  // ['block', …]
Figlet::font('/path/to/slant.flf');  // any standard FIGlet font
```

Ships the MIT `block` font as `Support\Fonts\BlockFont` (clean-room, ours),
registered in `Support\Fonts\BuiltinFonts`; bundles no third-party `.flf` fonts.
Add a built-in by adding a `*Font` class (returning a `Fonts\FontDefinition`) plus
one registry entry.

## BrailleCanvas

A virtual pixel grid: each terminal cell packs a **2×4 block of braille dots**
(`U+2800`-based), giving sub-character resolution for line and scatter charts. An
optional per-cell *pen* id lets callers colour cells (one colour per cell — a single
glyph can't mix colours per dot). Without Unicode it degrades to a coarse
one-char-per-cell ASCII plot.

```php
use Simtabi\Laranail\Console\Tools\Support\BrailleCanvas;

$canvas = new BrailleCanvas(width: 30, height: 8);  // cells (→ 60×32 pixels)
$canvas->set($x, $y);                                // light one pixel
$canvas->line($x0, $y0, $x1, $y1);                   // Bresenham line
$lines = $canvas->render();                          // list<string>
```

Used by [`LineChart` and `ScatterPlot`](charts.md); `pixelWidth()` / `pixelHeight()`
report the dot grid size.

## Hyperlink

Builds OSC-8 terminal hyperlinks with a scheme allow-list, degrading to
`label (url)` where hyperlinks or colour aren't supported. Used by the
[`Link`](typography.md) typography component and Markdown links.

```php
use Simtabi\Laranail\Console\Tools\Support\Hyperlink;

Hyperlink::render('Docs', 'https://opensource.simtabi.com/console/docs/');
Hyperlink::isAllowed($url);     // bool — scheme allow-list (http/https/mailto/…)
Hyperlink::safe($url);          // the URL if allowed, otherwise null
Hyperlink::sanitize($url);      // strip control chars + the OSC-8 ';' separator
Hyperlink::schemes();           // the allowed scheme list
```

## Config

A tiny defensive accessor for the package config that works even outside a booted
Laravel app (returns the default when the container isn't available). Used
internally by `Capabilities`, `Banner`, `Menu`, etc.

```php
use Simtabi\Laranail\Console\Tools\Support\Config;

Config::get('output.unicode', 'auto');   // reads config('console.output.unicode')
Config::locale();                         // console.locale, or null to follow the app locale
```

Validate the `console.*` config with [`Console::validateConfig()`](../configuration.md#validation)
(or the `laranail::console.check` command).

## Lang

Resolves widget strings from the `console::console.*` translations, honouring
`console.locale` **without** mutating the host app's global locale; falls back to the
given default (with `:placeholder` interpolation) when no translation exists.

```php
use Simtabi\Laranail\Console\Tools\Support\Lang;

Lang::get('summary.title', 'Summary');                       // translated, or 'Summary'
Lang::get('progress.eta', 'ETA :time', ['time' => '2m 18s']); // ':time' interpolated
```

## FileSize

Human-readable byte sizes — the single source of truth for byte formatting.

```php
use Simtabi\Laranail\Console\Tools\Support\FileSize;

FileSize::format(512);        // '512 B'
FileSize::format(1024);       // '1 KB'
FileSize::format(1536);       // '1.5 KB'
FileSize::format(1048576);    // '1 MB'
```

## NumberFormat

Compact numeric formatting shared by the chart/widget family: fixed-decimal rounding
with trailing zeros (and a dangling decimal point) trimmed.

```php
use Simtabi\Laranail\Console\Tools\Support\NumberFormat;

NumberFormat::trim(3.50);     // '3.5'
NumberFormat::trim(12.0);     // '12'
NumberFormat::trim(7.255, 2); // '7.26'
```

## Live

The live-render engine behind animated bars, spinners and in-place redraws is
`Support\Live` (`Console::live($output)`) — see
[Interactive & live](interactive.md) for `draw()` / `refresh()` / `animate()`.

[← Docs index](../../README.md#documentation)
