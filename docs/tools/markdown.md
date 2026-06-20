# Markdown rendering

`Console::markdown($md)` renders a documented Markdown subset to the terminal via
the [design-system theme](theming.md).

```php
echo Console::markdown($readme)->render();
```

## Supported

- **Headings** `#`–`######`
- **Paragraphs** with inline **bold**, *italic*, `` `code` `` and `[label](url)`
  (rendered with real ANSI styling; degrade to plain text / `label (url)` without
  colour). `:emoji:` shortcodes resolved.
- **Lists** — unordered (`-`/`*`), ordered (`1.`), task (`- [x]` / `- [ ]`)
- **Blockquotes** (`>`), **horizontal rules** (`---`)
- **Fenced code** ```` ```lang ```` — with basic php/json highlighting (other
  languages render plain)

Everything is responsive (wraps/clips to the terminal width).

## Not supported (v0.6)

Inline styling inside list items/blockquotes (those normalise to plain text),
tables, nested blockquotes, reference links. For full control compose a
[`Document`](../design-system.md) directly.

[← Docs index](../../README.md#documentation)
