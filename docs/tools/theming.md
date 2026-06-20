# Theming

The design system is driven by a **theme**: a semantic colour palette plus a
resolved style per UI element. Override the palette once and headings, links,
badges, banners and `Text` roles all follow it.

## The palette

Roles: `primary`, `accent`, `success`, `warning`, `danger`, `info`, `muted`.
Set them in config (any [colour spec](colors.md)):

```php
// config/console.php
'theme' => [
    'palette' => [
        'primary' => '#ec4899',
        'accent'  => '#22d3ee',
        'success' => '#16a34a',
    ],
],
```

`Console::theme()` returns the active [`Theme`](../../src/Console/Tools/Theme/Theme.php);
`$theme->color('primary')` reads a role, `$theme->style('h1')` returns the
[`Style`](colors.md) for an element.

## Element styles

Each element derives a sensible style from the palette: `h1`–`h6`, `paragraph`,
`link`, `quote`, `code`, `rule`, `list_marker`, and the status roles. Build a theme
inline and pass it where a theme is accepted:

```php
use Simtabi\Laranail\Console\Tools\Theme\Theme;

$theme = Theme::make(['primary' => '#ff0000']);
$themed = $theme->withStyle('paragraph', Console::style()->italic());

echo new Document(null, $theme)->h1('Hi')->paragraph('...')->render();
```

## Banner themes

Banners have named presets that draw from the palette — `Banner::success()`,
`->theme('error')`, etc. — plus custom presets in `config('console.banner.themes.*')`.
See [Banner designer](banner.md).

[← Docs index](../../README.md#documentation)
