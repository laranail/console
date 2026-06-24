# Banner designer

`Banner` (`Simtabi\Laranail\Console\Tools\Widgets\Banner`, `Console::banner()`)
renders a start-of-run masthead: a title — optionally as **FIGlet big-text** —
with an optional subtitle, aligned, colourised, and wrapped in a rule or a box.

Without a font it behaves exactly as before (a centred plain title), so existing
code is unaffected.

## Quick start

```php
use Simtabi\Laranail\Console\Tools\Enums\BorderStyle;use Simtabi\Laranail\Console\Tools\Widgets\Banner;

echo Banner::make('app v1.0')->subtitle('Simtabi')->boxed()->render();

echo Banner::make('DEPLOY')
    ->font('block')                          // big-text
    ->gradient(['#00ffff', '#ff00ff'])       // gradient fill
    ->align('center')
    ->border(BorderStyle::Double)
    ->render();
```

## Fluent API

| Method | Effect |
|--------|--------|
| `subtitle(string)` | a second, smaller line under the title |
| `font(?string)` | render the title as big-text via a bundled font name (e.g. `block`) or a `.flf` path; `null` = plain |
| `align('left'\|'center'\|'right')` | horizontal alignment (default `center`) |
| `color(string $hex)` | solid foreground colour |
| `gradient(array $stops)` | per-character gradient across ≥ 2 hex stops |
| `border(BorderStyle)` | frame in a box of that border family (implies boxed) |
| `boxed(bool=true)` | frame in a box (default border) |
| `padding(int)` | interior padding when boxed |
| `width(int)` | inner content width (auto-grows to fit big-text) |
| `render()` / `__toString()` | the finished string |

Colour is gated by [`Capabilities`](support.md#capabilities) (degrades / strips
when unsupported), and alignment is measured by display width, so ANSI never
skews it.

## Fonts

The FIGlet renderer is [`Support\Figlet`](support.md#figlet). It ships one built-in font,
**`block`** — a uniform 5×5 block font (`Support\Fonts\BlockFont`, MIT/ours) registered in
`Support\Fonts\BuiltinFonts` — and also parses standard FIGlet **`.flf`** files by path:

```php
echo Banner::make('HELLO')->font('block')->render();          // bundled
echo Banner::make('HELLO')->font('/path/to/slant.flf')->render(); // any .flf
```

`Figlet::builtins()` lists bundled fonts. A **missing font**, or big-text **too
wide for the terminal**, falls back to the plain title — banners never overflow or
throw.

## Configuration

`config/console.php`:

```php
'banner' => [
    'font'  => env('CONSOLE_BANNER_FONT'),   // default font name / .flf path, or null
    'width' => env('CONSOLE_BANNER_WIDTH'),  // default inner width, or null to auto-fit
],
```

[← Docs index](../../README.md#documentation)
