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

## Presets

Five built-in palettes ship ready to use: **`dracula`, `nord`, `solarized`,
`monochrome`, `github`**. Select one in config (the `palette` block still overrides
individual roles on top), or build a theme from one directly:

```php
// config/console.php — preset as the base, palette overrides on top
'theme' => [
    'preset'  => 'nord',                 // or env('CONSOLE_THEME_PRESET')
    'palette' => ['primary' => '#ec4899'],
],
```

```php
use Simtabi\Laranail\Console\Tools\Theme\Theme;

$theme = Theme::preset('dracula');       // throws on an unknown preset name
echo Console::document(null, $theme)->h1('Hi')->render();
```

An unknown preset in **config** falls back to the default palette (and is reported
by [config validation](../configuration.md)); `Theme::preset()` called directly
throws `InvalidArgumentException`.

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
