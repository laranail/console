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
| `spinner.frames` | `braille` | `braille` \| `dots` \| `line` \| `breath`. Env: `CONSOLE_SPINNER_FRAMES`. |
| `progress.format` | `detailed` | `minimal` \| `classic` \| `detailed`. Env: `CONSOLE_PROGRESS_FORMAT`. |
| `progress.glyphs` | `blocks` | `blocks` \| `ascii` \| `dots` \| `arrows` \| `gradient`. Env: `CONSOLE_PROGRESS_GLYPHS`. |
| `emoji.mode` | `auto` | `auto` \| `unicode` \| `ascii`. Env: `CONSOLE_EMOJI_MODE`. |
| `emoji.custom` | `[]` | Extra/override emoji: `name => [unicode, ascii]` or a single string. |
| `banner.font` | `null` | Default FIGlet font name (e.g. `block`) or `.flf` path; `null` = plain title. Env: `CONSOLE_BANNER_FONT`. |
| `banner.width` | `null` | Default banner inner width, or `null` to auto-fit. Env: `CONSOLE_BANNER_WIDTH`. |
| `summary.divider_width` | `60` | `Console::summary()` top-rule length. |
| `summary.label_pad` | `16` | Column width the metric labels are padded to. |
| `summary.message_max` | `80` | Error messages longer than this are truncated (with `…`). |
| `summary.rate_good` | `100` | Success-rate ≥ this renders green. |
| `summary.rate_warn` | `80` | Success-rate ≥ this renders yellow (else red). |
| `menu.foreground` | `null` | `Console::menu()` frame colour (hex/name) or `null`. Env: `CONSOLE_MENU_FG`. |
| `menu.width` | `null` | `Console::menu()` frame width, or `null` to auto-fit. Env: `CONSOLE_MENU_WIDTH`. |
| `links.allowed_schemes` | `['http','https','mailto']` | URL schemes permitted in terminal hyperlinks; others render as plain text. |
| `logging.redact_keys` | `['password','secret','token','key','authorization']` | Context keys scrubbed before an exception is logged. |
| `logging.trace_in_debug_only` | `true` | Only log stack traces when `app.debug` is true. |
| `logging.channel` | `null` | Log channel name, or `null` for the default. Env: `CONSOLE_LOG_CHANNEL`. |
| `interaction.non_interactive_required_throws` | `true` | Throw (rather than returning empty) when a required value is requested in a non-interactive session. Env: `CONSOLE_REQUIRE_INTERACTIVE`. |
| `locale` | `null` | Translation locale for console strings; `null` follows the app locale. Env: `CONSOLE_LOCALE`. |

`auto` values are resolved at runtime by
`Simtabi\Laranail\Console\Tools\Support\Capabilities`, which inspects the
terminal (TTY, `COLORTERM`, `TERM`, `WT_SESSION`, locale) and the standard
`NO_COLOR` / `FORCE_COLOR` environment variables.

[← Docs index](../README.md#documentation)
