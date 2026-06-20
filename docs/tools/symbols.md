# Symbols

`Console::symbol('name')` returns a glyph for the current terminal — Unicode when
supported, an ASCII fallback otherwise (resolved via [`Capabilities`](support.md)).

```php
Console::symbol('arrow');    // → (or -> in ascii)
Console::symbol('success');  // ✓ (or [OK])
```

Used throughout the toolkit (tree connectors, status glyphs, list markers, the
`Text` builder's `->symbol()`). For the full set, see
`Support\Symbols`.

[← Docs index](../../README.md#documentation)
