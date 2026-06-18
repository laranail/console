# Configuration

Publish the config file to customise defaults:

```bash
php artisan vendor:publish --tag=console-config
```

This writes `config/console.php`. Every key has a sensible default, and most
accept an environment override.

| Key | Default | Notes |
|-----|---------|-------|
| `output.unicode` | `auto` | `auto` \| `true` \| `false`. Use Unicode glyphs or ASCII fallbacks. Env: `CONSOLE_UNICODE`. |
| `output.colors` | `auto` | `auto` \| `always` \| `never`. `auto` honours `NO_COLOR`/`FORCE_COLOR`/TTY. Env: `CONSOLE_COLORS`. |
| `output.symbols` | `auto` | `auto` \| `fancy` \| `ascii`. Status/tree/box glyph set. |
| `output.width` | `null` | Fixed column width, or `null` to auto-detect. Env: `CONSOLE_WIDTH`. |
| `spinner.frames` | `braille` | `braille` \| `dots` \| `line` \| `breath`. |
| `spinner.interval_ms` | `80` | Frame interval for manual spinners. |
| `progress.format` | `detailed` | `minimal` \| `classic` \| `detailed`. |
| `progress.glyphs` | `blocks` | `blocks` \| `ascii` \| `dots` \| `arrows` \| `gradient`. |
| `links.allowed_schemes` | `['http','https','mailto']` | URL schemes permitted in terminal hyperlinks; others render as plain text. |
| `logging.redact_keys` | `['password','secret','token','key','authorization']` | Context keys scrubbed before an exception is logged. |
| `logging.trace_in_debug_only` | `true` | Only log stack traces when `app.debug` is true. |
| `logging.channel` | `null` | Log channel name, or `null` for the default. |
| `interaction.non_interactive_required_throws` | `true` | Throw (rather than returning empty) when a required value is requested in a non-interactive session. |
| `locale` | `null` | Translation locale for console strings; `null` follows the app locale. |

`auto` values are resolved at runtime by
`Simtabi\Laranail\Console\Tools\Support\Capabilities`, which inspects the
terminal (TTY, `COLORTERM`, `TERM`, `WT_SESSION`, locale) and the standard
`NO_COLOR` / `FORCE_COLOR` environment variables.

[← Docs index](../README.md#documentation)
