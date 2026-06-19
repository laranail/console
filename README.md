# laranail/console

[![Latest version on Packagist](https://img.shields.io/packagist/v/laranail/console.svg)](https://packagist.org/packages/laranail/console)
[![Tests](https://github.com/laranail/console/actions/workflows/tests.yml/badge.svg)](https://github.com/laranail/console/actions/workflows/tests.yml)
[![Static analysis](https://github.com/laranail/console/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/laranail/console/actions/workflows/static-analysis.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A Rich-class console toolkit for Laravel. One package, two namespaces:

- **`Console\Tools`** — terminal **output**: a fluent formatter, spinners,
  flavoured progress bars, boxes, trees, tables, banners, gauges, sparklines,
  a multi-task progress widget, plus an enhanced Artisan command base.
- **`Console\Prompter`** — terminal **input**: a fluent wrapper over
  `laravel/prompts` with a form builder and 25 validators.

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

// Inside an Artisan command you have $this->output; elsewhere use any
// Symfony OutputInterface (e.g. new ConsoleOutput()).

// Output — markup-bearing helpers go through the output:
$output->writeln(Console::status()->success('Build complete'));

// Plain renderers return finished strings and echo fine:
echo Console::box(['Name: app', 'Env:  prod'])->title('Config')->render();
echo Console::tree('project')->child('src', fn ($t) => $t->child('Console'))->render();
echo Console::gauge(72, 100)->label('Disk')->showValue()->render();

Console::spinner('Compiling…')->run(fn () => compile());

$bar = Console::progress($output, max: count($items))->format('detailed')->glyphs('blocks');
$bar->start();
foreach ($items as $item) { process($item); $bar->advance(); }
$bar->finish();

// Multi-task progress (exits non-zero if any task fails)
$tasks = Console::tasks($output);
$tasks->task('Compile', 100)->advance(100)->succeed();
exit($tasks->finish());

// Input
$name = Console::prompter()->text('Your name', required: true)->getResult();
```

`Console::ui()` returns the underlying fluent `ConsoleUIFormatter`;
`Console::prompter()` returns a fresh `Prompter` per call. The two sub-domains
are fully decoupled — they only meet in the `Console` aggregator. See the
[runnable examples](examples/) for end-to-end demos.

### Writing through an output

The package has three output styles — know which you're holding:

| Style | APIs | How to print |
|-------|------|--------------|
| **Symfony markup** (e.g. `<fg=green>…</>`) | `Console::status()`, `ConsoleUIFormatter::success()/format()/badge()` | `$output->writeln(...)` / `$this->line(...)` — renders colour on a TTY, stripped when piped. Echoing prints literal tags. |
| **Finished strings** | `box`, `tree`, `table`, `rule`, `gauge`, `sparkline`, `banner`, `steps`, `callout`, and `Color`/`colorize()` (raw ANSI, echo-safe) | `echo` or `writeln` both fine. |
| **Self-writing** | `spinner`, `progress`, `tasks` | They write to the output you pass them. |

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
| [Output formatter](docs/tools/formatting.md) | Colour/badge/link string primitives |
| [Output widgets](docs/tools/widgets.md) | Spinner, progress (+ETA), box, tree, table, gauge, summary… |
| [Banner designer](docs/tools/banner.md) | FIGlet big-text, alignment, colour/gradient, borders |
| [Support utilities](docs/tools/support.md) | Capabilities, Color, DisplayWidth, Emoji, Figlet, TimeFormat |
| [Commands](docs/tools/commands.md) | The Artisan command base + services |
| [Runners](docs/tools/runners.md) | Conditional console execution |
| [Notifications](docs/tools/notifications.md) | The console channel |
| [Observers & events](docs/tools/observers-events.md) | Command lifecycle hooks |
| [Prompts & forms](docs/tools/prompter.md) | The Prompter, forms and validators |

Changelog: [CHANGELOG.md](CHANGELOG.md).

## Local development

```bash
composer install
composer test                 # vendor/bin/pest
composer lint                 # pint + phpstan + rector --dry-run
composer audit                # composer audit (security advisories)
```

## Sister packages

- [`laranail/database-tools`](https://github.com/laranail/database-tools) — standalone Laravel database utilities (traits, casts, schema macros, backup).
- [`laranail/package-tools`](https://github.com/laranail/package-tools) — runtime base library for building Laravel packages.
- [`laranail/package-scaffolder`](https://github.com/laranail/package-scaffolder) — generator that scaffolds new packages.
- [`laranail/laranail`](https://github.com/laranail/laranail) — Simtabi's Laravel utility toolbox.

## Contributing & security

- [CONTRIBUTING.md](CONTRIBUTING.md) — development guidelines and PR expectations.
- [SECURITY.md](SECURITY.md) — how to report a vulnerability (opensource@simtabi.com).
- [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) — community expectations.

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
