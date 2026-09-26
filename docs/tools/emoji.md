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

## Width

`DisplayWidth` measures text one grapheme cluster at a time, so an emoji
sequence — a ZWJ family, a flag, a keycap, a skin tone, anything carrying VS16 —
is two columns and is never cut apart by `truncate()` or `truncateAnsi()`.
Symfony's width alone counts a ZWJ family as 8. Checked against every emoji in
Unicode 18, console agrees with the catalogue on 3956 of 3972 by itself; the 16
left are Unicode 17–18 codepoints newer than Symfony's width table, and they
measure correctly once `laranail/emojis` is installed.

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
| `DisplayWidth::of('👨‍👩‍👧‍👦')` | `2` | `2` |
| `DisplayWidth::of('🫪')` (Unicode 17) | `1` | `2` |

Names resolve in this order: `custom`, then console's map, then the catalogue.
Console's map wins for its own names because several differ on purpose — here
`cross` is ❌ and `stop` is 🛑, where the catalogue has ✝️ and ⏹️. Mode is still
decided by `laranail.console.emoji.mode`, not by the emojis package's own
terminal detection, and `all()` then includes the catalogue's shortcodes
(several thousand).

Console owns the `Tools\Contracts\EmojiCatalogue` contract and emojis ships its
implementation, so console never depends on emojis — the dependency runs the
other way. If the application binds its own `Emojis` instance (the emojis
service provider does), that instance is used, so custom shortcodes registered
there resolve through `Console::emoji()` as well. Any failure inside the
catalogue falls back to the built-in behaviour rather than throwing.

[← Docs index](../../README.md#documentation)
