# Emoji

`Console::emoji()` resolves `:shortcode:` names to Unicode emoji or an ASCII
fallback, decided by mode (`auto` follows Unicode capability).

```php
Console::emoji()->get('rocket');                  // 🚀  (or "->" in ascii mode)
Console::emoji()->render('Done :white_check_mark:');
Console::emoji()->ascii()->render('Done :tada:'); // "Done \o/"
Console::emoji()->with(['deploy' => ['🛳️', '>>']])->get('deploy');
```

Methods: `auto()/unicode()/ascii()` (mode), `with($map)` (register/override),
`has()/get()/render()/strip()/all()`. Configure globally via
`config('laranail.console.emoji.mode')` and `config('laranail.console.emoji.custom')`.
`:emoji:` shortcodes are also resolved inside paragraphs, `Text` and Markdown.

## With `laranail/emojis`

Console ships a curated map of about 70 names. Install the optional
[`laranail/emojis`](https://opensource.simtabi.com/documentation/laranail/emojis/)
package and every shortcode in the Unicode catalogue resolves too — no
configuration, console detects it.

```bash
composer require laranail/emojis
```

| | Without | With `laranail/emojis` |
|---|---|---|
| `get('unicorn')` | `''` | `🦄` (ASCII mode: `:unicorn:`) |
| `render('ship 🚀')` in ASCII mode | `ship 🚀` | `ship ->` |
| `DisplayWidth::of('👨‍👩‍👧‍👦')` | `8` | `2` |

Names resolve in this order: `custom`, then console's map, then the catalogue.
Console's map wins for its own names because several differ on purpose — here
`cross` is ❌ and `stop` is 🛑, where the catalogue has ✝️ and ⏹️. Mode is still
decided by `laranail.console.emoji.mode`, not by the emojis package's own
terminal detection, and `all()` then includes the catalogue's shortcodes
(several thousand).

If the application binds its own `Emojis` instance (the emojis service provider
does), console uses that one, so custom shortcodes registered there resolve
through `Console::emoji()` as well. Any failure inside the catalogue falls back to
the built-in behaviour rather than throwing.

[← Docs index](../../README.md#documentation)
