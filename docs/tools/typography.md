# Typography

Themeable, responsive text components (`Tools\Typography`). Each implements
`Renderable` + `Stringable`, so it `echo`s directly, composes inside a
[`Panel`](panel.md), and is gathered by a [`Document`](markdown.md).

| Component | Facade | Notes |
|-----------|--------|-------|
| `Heading` | `Console::heading($t, $level)` | levels 1–6; h1/h2 get an underline rule |
| `Paragraph` | `Console::paragraph($t)` | word-wrap; `align(left\|center\|right\|justify)`; resolves `:emoji:` |
| `ListBlock` | `Console::list($items)` | `->ordered()`, `->tasks([label=>bool])`, `->definition([term=>desc])` |
| `Link` | `Console::link($label, $url)` | OSC-8 via [`Hyperlink`](support.md) + `label (url)` fallback |
| `Quote` | `Console::quote($t)` | inline, themed quotation marks |
| `BlockQuote` | `Console::blockQuote($t)` | left bar + wrapped body |
| `Code` / `CodeBlock` | `Console::code()` / `Console::codeBlock()` | inline / fenced (optional `->caption()`) |
| `Text` | `Console::text($t)` | fluent inline builder (below) |

```php
echo Console::heading('Report', 1)->render();
echo Console::paragraph($longText)->align('justify')->width(60)->render();
echo Console::list(['one', 'two'])->ordered()->render();
echo Console::link('Docs', 'https://opensource.simtabi.com/console/docs/')->render();
echo Console::blockQuote('Make the easy things easy.')->render();
```

## <a name="text"></a>Text

`Text` unifies colour/style + emoji + symbols + theme roles into one echo-safe
string:

```php
echo Console::text('Deploying ')
    ->emoji('rocket')
    ->fg('#7c3aed')->bold()
    ->render();

echo Console::text('Saved')->success()->render();   // theme role
```

Setters: `text() emoji() symbol() space() fg() bg() bold() dim() italic()
underline() strikethrough()` and roles `success() warning() danger() info()
muted()`.

All components wrap/clip to the terminal width by default — see
[Responsive output](../responsive.md).

[← Docs index](../../README.md#documentation)
