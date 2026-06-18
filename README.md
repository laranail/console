# laranail/console

[![Latest version on Packagist](https://img.shields.io/packagist/v/laranail/console.svg)](https://packagist.org/packages/laranail/console)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A Rich-class console toolkit for Laravel. One package, two namespaces:

- **`Console\Tools`** — terminal **output**: a fluent formatter, spinners,
  flavoured progress bars, boxes, trees, tables, banners, gauges, sparklines,
  a multi-task progress widget, plus an enhanced Artisan command base.
- **`Console\Prompter`** — terminal **input**: a fluent wrapper over
  `laravel/prompts` with a form builder and 25+ validators.

Targets PHP `^8.3` (8.3–8.5) on Laravel `^13`.

## Install

```bash
composer require laranail/console
```

The service provider, the `Console` and `Prompter` facades, and the global
`prompter()` helper are auto-discovered. Publish the config and language files
if you want to customise them:

```bash
php artisan vendor:publish --tag=console-config
php artisan vendor:publish --tag=console-lang
```

## Quick start

```php
use Simtabi\Laranail\Console\Facades\Console;

// Output
echo Console::status()->success('Build complete');
echo Console::box(['Name: app', 'Env:  prod'])->title('Config')->render();
echo Console::tree('project')->child('src', fn ($t) => $t->child('Console'))->render();
echo Console::gauge(72, 100)->label('Disk')->showValue()->render();

Console::spinner('Compiling…')->run(fn () => compile());

$bar = Console::progress(max: count($items))->format('detailed')->glyphs('blocks');
$bar->start();
foreach ($items as $item) { process($item); $bar->advance(); }
$bar->finish();

// Multi-task progress (exits non-zero if any task fails)
$tasks = Console::tasks();
$tasks->task('Compile', 100)->advance(100)->succeed();
exit($tasks->finish());

// Input
$name = Console::prompter()->text('Your name', required: true)->getResult();
```

`Console::ui()` returns the underlying fluent `ConsoleUIFormatter`;
`Console::prompter()` returns the shared `Prompter`. The two sub-domains are
fully decoupled — they only meet in the `Console` aggregator.

## Security & portability

- All rendered text is stripped of terminal control characters (no ANSI/`\r`
  output spoofing); terminal hyperlinks are limited to an allow-list of URL
  schemes.
- Validators are total — non-string input returns an error rather than throwing.
- Unicode glyphs and colour degrade gracefully: capabilities are detected from
  the terminal and honour `NO_COLOR`, `FORCE_COLOR`, `TERM` and locale; ASCII
  fallbacks are used when Unicode is unavailable.
- Signal handling is guarded by `ext-pcntl`, so commands run on Windows too.

## <a name="documentation"></a>Documentation

| Page | What it covers |
|------|----------------|
| [Installation](docs/installation.md) | Requirements, install, publishing |
| [Architecture](docs/architecture.md) | Umbrella, sub-domains, the manager |
| [Configuration](docs/configuration.md) | Every `config/console.php` key |
| [Internationalization](docs/i18n.md) | Translating console strings |
| [Output formatter](docs/tools/formatting.md) | Colours, badges, status, links |
| [Output widgets](docs/tools/widgets.md) | Spinner, progress, box, tree, table, gauge… |
| [Commands](docs/tools/commands.md) | The Artisan command base + services |
| [Runners](docs/tools/runners.md) | Conditional console execution |
| [Notifications](docs/tools/notifications.md) | The console channel |
| [Observers & events](docs/tools/observers-events.md) | Command lifecycle hooks |
| [Prompts & forms](docs/tools/prompter.md) | The Prompter, forms and validators |

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
