# Markdown rendering

`Console::markdown($md)` renders a documented Markdown subset to the terminal via
the [design-system theme](theming.md).

```php
echo Console::markdown($readme)->render();
```

## Supported

- **Headings** `#`–`######`
- **Inline emphasis** — **bold**, *italic*, `` `code` `` and `[label](url)` render
  with real ANSI styling in **paragraphs, list items and blockquotes** alike;
  they degrade to plain text / `label (url)` without colour. `:emoji:` shortcodes
  resolved throughout. (Headings use the plain normalisation.)
- **Lists** — unordered (`-`/`*`), ordered (`1.`), task (`- [x]` / `- [ ]`)
- **Blockquotes** (`>`), **horizontal rules** (`---`)
- **Tables** — GFM pipe tables (header + `---|---` separator + rows) render via the
  [Table widget](barchart.md) (responsive; columns shrink to fit, never dropped).
  Leading/trailing pipes are optional; ragged rows pad to the header column count.
- **Fenced code** ```` ```lang ```` — with basic highlighting for **php, json,
  bash, yaml and js** (aliases: `sh`/`shell`/`zsh`, `yml`, `javascript`/`node`);
  other languages render plain.

Everything is responsive (wraps/clips to the terminal width).

## Not supported

Table cells carry plain text (inline markers stripped); nested blockquotes,
reference-style links, and inline HTML. For full control compose a
[`Document`](../design-system.md) directly.

[← Docs index](../../README.md#documentation)
