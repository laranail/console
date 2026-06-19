# Third-party notices

`laranail/console` is MIT licensed (© Simtabi LLC). It includes work adapted from
the projects below; their licenses and copyright are retained here.

## ajaxray/ansikit — MIT, © Anis Uddin Ahmad

The multi-column layout engine and the terminal/keypress helpers are hardened
ports (not verbatim copies) of components from
[ajaxray/ansikit](https://github.com/ajaxray/ansikit):

- `Tools\Widgets\Panel`, `Tools\Widgets\PanelBlock`, `Tools\Contracts\Renderable`
  — from AnsiKit's `Panel` / `PanelBlock` / `Renderable`.
- `Tools\Support\Keypress` — from AnsiKit's `Support\Keypress`.
- `Tools\Support\Terminal` — expands AnsiKit's `Support\Util` (bell, tab title).

Changes on import: re-namespaced; widths/truncation routed through `DisplayWidth`
(multibyte/ANSI-aware) instead of `substr`; borders via `BorderStyle` with an
ASCII fallback; capability-gated output; `strict_types` and full type hints.

> MIT License — Copyright (c) Anis Uddin Ahmad <https://www.ajaxray.com>.
> Permission is hereby granted, free of charge, to any person obtaining a copy of
> this software and associated documentation files (the "Software"), to deal in
> the Software without restriction… (full MIT text applies).

## nunomaduro/laravel-console-menu — MIT, © Nuno Maduro

The interactive `Tools\Widgets\Menu` mirrors the ergonomic API of
[nunomaduro/laravel-console-menu](https://github.com/nunomaduro/laravel-console-menu)
(`->menu($title, $options)->open()`, `addOption()`, `addQuestion()`, and the
`Command::macro('menu')`). Our implementation is independent — built natively on
our Keypress reader with a `laravel/prompts` fallback (no `php-school/cli-menu`
dependency) — so the menu works without `ext-posix` and on Windows.

## bramus/ansi-php — MIT, © Bram(us) Van Damme

The ANSI primitive tables (`Tools\Support\Sgr`, `Tools\Support\ControlChars`,
`Tools\Support\Csi`) were **re-derived from the ECMA-48 specification** (numeric
codes are facts, not copyrightable) after reviewing
[bramus/ansi-php](https://github.com/bramus/ansi-php). No code was copied.

## FIGlet fonts

The bundled `resources/fonts/block.php` font is original work (© Simtabi LLC,
MIT). The FIGlet `.flf` renderer parses standard FIGlet fonts but the package
bundles none; see `resources/fonts/LICENSE`.
