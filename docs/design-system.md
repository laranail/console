# The design system

`laranail/console` is a mini design system for the terminal: a themeable
stylesheet, typography components, a document composer, and an interactive/live
layer — all **responsive** (clamp to the terminal width) and **degradation-safe**
(truecolor → 256 → 16 → none; unicode → ascii; TTY → static).

## Layers

| Layer | What | Entry points |
|-------|------|--------------|
| **Tokens** | Semantic palette + per-element styles | `Console::theme()` · `config('console.theme.palette')` |
| **Primitives** | Colour, Style, Emoji, Symbols, Align, Os | `Console::color()/style()/emoji()/symbol()/os()` |
| **Typography** | Heading, Paragraph, List, Link, Quote, BlockQuote, Code, Text | `Console::heading()/paragraph()/list()/link()/quote()/blockQuote()/code()/codeBlock()/text()` |
| **Documents** | Page composer + Markdown subset | `Console::document()` · `Console::markdown()` |
| **Widgets** | Box, Table, Panel, Banner, BarChart, badges… | `Console::box()/table()/panel()/banner()/barChart()/badge()/pill()` |
| **Interactive** | Live engine, animated bar, buttons | `Console::live()/animatedBar()/button()/buttonGroup()` |

## Theming

Override the palette once and the whole UI re-skins:

```php
// config/console.php
'theme' => ['palette' => ['primary' => '#ec4899', 'accent' => '#22d3ee']],
```

Every heading, link, badge, banner and `Text::…->success()` follows it. Build a
theme inline with `Theme::make(['primary' => '#…'])` and pass it to a component or
`Document`.

## Compose a document

```php
echo Console::document()
    ->h1('Release notes')
    ->paragraph('Highlights for this version:')
    ->bulletList(['Responsive output', 'Themeable design tokens'])
    ->taskList(['Shipped' => true, 'Docs' => false])
    ->blockQuote('Make the easy things easy.')
    ->codeBlock("Console::document()->h1('Hi')->render();")
    ->add(Console::barChart(['api' => 1240, 'web' => 860]))
    ->render();
```

Or render Markdown: `echo Console::markdown($readme)->render();`

## Responsiveness

On by default — widgets clamp to the detected terminal width. An explicit
`->width()` always wins; `->responsive(false)` opts a widget out; set
`config('console.responsive') = false` to disable globally. See
[Responsive output](responsive.md).

## Graceful degradation

- **Colour**: `Color`/`Style` quantize 24-bit → xterm-256 → ANSI-16, and strip
  entirely under `NO_COLOR` / non-TTY.
- **Glyphs/emoji**: Unicode glyphs fall back to ASCII (`Capabilities`/`Os`).
- **Interactivity**: `Live`, `AnimatedBar` and `Button` render a single static
  frame when piped/CI — no cursor sequences.

## Testing

Use `Simtabi\Laranail\Console\Testing\InteractsWithConsole` to force capabilities
(`withConsoleCapabilities(unicode: false, width: 40)`) and script prompts. See
[Testing](tools/testing.md).

[← Docs index](../README.md#documentation)
