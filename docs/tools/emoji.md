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
`config('console.emoji.mode')` and `config('console.emoji.custom')`. `:emoji:`
shortcodes are also resolved inside paragraphs, `Text` and Markdown.

[← Docs index](../../README.md#documentation)
