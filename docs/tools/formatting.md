# Formatting

`ConsoleUIFormatter` (under `Simtabi\Laranail\Console\Tools\Formatting`) is the
low-level **text-formatting primitive**: colours, backgrounds, text styles,
badges and links for a single string. Colour/Unicode support is auto-detected via
the shared [Capabilities](support.md#capabilities).

> Composite, multi-line UI components are **widgets**, not formatter methods.
> Status lines → [`StatusLine`](widgets.md#status-rule-box-tree); execution
> reports → [`Summary`](widgets.md#summary--header); section titles →
> [`Header`](widgets.md#summary--header); trees → [`Tree`](widgets.md); progress →
> [`ProgressBar`](widgets.md#progress-bar); tree glyphs →
> [`Symbols`](support.md#symbols). The formatter keeps only single-string primitives.

> For a **fluent writer** that styles *and writes* (chainable, with ready-to-use
> context statuses and emoji), see the [Console writer](writing.md)
> (`Console::writer()`). `ConsoleUIFormatter` returns strings; `ConsoleWriter` writes them.

## Fluent builder

Every method returns the builder, so a whole style reads as one chain:

```php
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

$text = ConsoleUIFormatter::create()
    ->txtColorRed()->bgColorWhite()->bold()
    ->message('✓ Deployed 🚀')
    ->padding(2)          // inside the background
    ->lineHeight(3)       // blank background lines above and below
    ->addSpaceBefore(2)   // outside the background
    ->addSpaceAfter(1, ConsoleUIFormatter::TAB);

$text->write($output);             // or $output->writeln((string) $text)
```

The builder is `Stringable`. Pass it wherever `string|Stringable` is accepted.
Where a plain `string` parameter is declared, cast it with `(string)`: under
`strict_types`, Symfony's `writeln()` rejects the object itself.

| Method | Does |
|---|---|
| `message(string\|Stringable)` | Sets the text. `:shortcodes:` resolve through [`Emoji`](support.md#emoji). |
| `addMessage(string)` | Sets the text exactly as given, without shortcode resolution. |
| `icon(string $name)` | Puts an emoji, by name, in front of the message with one space. An unknown name adds nothing. |
| `txtColor<Name>()` / `bgColor<Name>()` | Foreground or background from a named colour, e.g. `txtColorBrightRed()`, `bgColorSlate()`. |
| `fg(string)` / `bg(string)` | Any colour: a Symfony name, a [`Color`](colors.md) name, `#hex`, `rgb()`, `hsl()` or `@N`. |
| `bold()`, `underline()`, `blink()`, `reverse()`, `conceal()` | Text options. `addTextStyles()` takes the same names, plus the `underline` and `hidden` aliases. |
| `padding(int)` | Spaces either side of the message, inside its background. |
| `lineHeight(int)` | Total height in lines. The message sits in the middle; the odd extra line goes below. |
| `addSpaceBefore(int = 1, string = SPACE)` | Whitespace before the message, outside its background. Pass `ConsoleUIFormatter::TAB` for tabs. Calls add up. |
| `addSpaceAfter(int = 1, string = SPACE)` | The same, after the message. |
| `href(string)` | Makes the text a link. The URL goes through the [`Hyperlink`](support.md#hyperlink) scheme allow-list. |
| `markup(bool = true)` | Treats formatter tags inside the message as markup. Off by default. |
| `capabilities(Capabilities)` | Pins the emoji fallback and `toAnsi()` instead of detecting the terminal. |
| `render()` / `(string)` | Symfony markup, one tag per line. |
| `write(OutputInterface)` | Renders and writes. |
| `toAnsi(?Capabilities)` | Raw ANSI, ready to `echo`. Plain text without colour support. |
| `reset()` | Clears everything, layout and spacing included. |

### Names for `txtColor<Name>()` and `bgColor<Name>()`

There are 38 names, each available both as `txtColor<Name>()` and as `bgColor<Name>()`.

- **Symfony's own palette (17).** These pass through as names, so they follow the terminal's theme:
  `Black`, `Red`, `Green`, `Yellow`, `Blue`, `Magenta`, `Cyan`, `White`, `Default`, `Gray`,
  and `BrightRed` through `BrightWhite`.
- **The [`Color`](colors.md) palette (21).** These are sent as hex:
  `Lime`, `Grey`, `Silver`, `Maroon`, `Olive`, `Navy`, `Teal`, `Purple`, `Orange`, `Pink`,
  `Brown`, `Gold`, `Slate`, `Indigo`, `Violet`, `Crimson`, `Coral`, `Salmon`, `Turquoise`,
  `Aqua`, `Mint`.

Each one is declared as an `@method`, so IDEs complete them. `SYMFONY_COLORS` lists the
names that pass through.

### Errors surface where they are set

An unknown colour throws `InvalidColorException` when you set it. An unknown text style,
`padding(-1)`, `lineHeight(0)`, or a space count below 1 throws
`InvalidArgumentException`. Symfony's formatter would otherwise throw only when the
string is finally written, far from the call that caused it.

### Emoji

`message()` and `icon()` resolve names through [`Emoji`](support.md#emoji). It serves
every Unicode shortcode when the optional `laranail/emojis` package is installed,
and console's built-in map otherwise. On a terminal without Unicode it falls back to
ASCII (`🚀` becomes `->`). Widths are measured per grapheme, so a two-column emoji
still lines up with `padding()` and `lineHeight()`.

Names resolve when the string is rendered, not when `message()` or `icon()` is called,
so `capabilities()` applies wherever it sits in the chain. With `laranail/emojis`
installed, a name only its catalogue knows (`:unicorn:`) resolves too; console's own
map still wins for the names it defines, as in `Emoji`.

```bash
composer require laranail/emojis   # optional: the full Unicode catalogue
```

### Message text is escaped

Tags inside the message are shown literally, so a message carrying `</>` or
`<fg=red>` cannot end or change the style. Call `markup()` when the message is
trusted markup of your own.

## Static helpers

- **Markup**: `success()`, `error()`, `warning()`, `info()`, `format()`,
  `badge()`, `badges()`, `link()` and `hex()` return Symfony Console markup
  (`<fg=green>…</>`). Write them **through an output** so the colour renders;
  echoing them prints the tags literally.

  ```php
  $output->writeln(ConsoleUIFormatter::success('Done!'));
  $output->writeln(ConsoleUIFormatter::badge('NEW', ConsoleUIFormatter::BADGE_STYLE_SUCCESS));
  $output->writeln(ConsoleUIFormatter::link('Docs', 'https://example.com')); // OSC-8, scheme allow-listed
  ```

- **Raw ANSI (echo-safe)**: `colorize()` emits a coloured string ready to print. It
  accepts the `BRIGHT_*` constants.

  ```php
  echo ConsoleUIFormatter::create()->colorize('OK', ConsoleUIFormatter::GREEN, bold: true);
  ```

The colour, style and badge constants (`GREEN`, `BRIGHT_RED`, `BOLD`, `BADGE_STYLE_*`,
`SPACE`, `TAB`, and so on) and the `ANSI_COLORS` map are public on the class.
`getBadgeStyles()` lists the badge styles. All input is stripped of terminal control
characters by `sanitizeText()`.

A runnable tour is in `examples/tools/formatting.php`.

## Progress, reports, status lines

These moved to the widget layer:

- Execution summaries → `Summary::make($stats)->render()` ([widgets](widgets.md#summary--header)).
- Section headers → `Header::make($title)->count($n)->render()`.
- Status lines → `Console::status()->success(...)` ([`StatusLine`](widgets.md)).
- Progress → the flavoured [`ProgressBar` widget](widgets.md#progress-bar)
  (`Console::progress()`) — percent/ETA/rate, glyph styles, instance-scoped
  placeholders.

[← Docs index](../../README.md#documentation)
