# laranail/console

[![Latest version on Packagist](https://img.shields.io/packagist/v/laranail/console.svg)](https://packagist.org/packages/laranail/console)
[![Tests](https://github.com/laranail/console/actions/workflows/tests.yml/badge.svg)](https://github.com/laranail/console/actions/workflows/tests.yml)
[![Static analysis](https://github.com/laranail/console/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/laranail/console/actions/workflows/static-analysis.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A Rich-class console toolkit for Laravel. One package, two namespaces:

- **`Console\Tools`** — terminal **output**: a fluent formatter, spinners,
  flavoured progress bars, boxes, trees, tables, banners, gauges, sparklines,
  charts (bar/column/line/scatter/heatmap/histogram/stacked), a typography + Markdown
  layer, a multi-task progress widget, plus an enhanced Artisan command base.
- **`Console\Prompter`** — terminal **input**: a fluent wrapper over
  `laravel/prompts` with a form builder and 26 validators.

Targets PHP `^8.4.1` (8.4.1–8.5) on Laravel `^13` (Symfony 8).

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
| **Finished strings** | `box`, `tree`, `table`, `panel`, `columns`, `keyValue`, `rule`, `gauge`, `sparkline`, `barChart`, `columnChart`, `lineChart`, `scatterPlot`, `heatmap`, `histogram`, `stackedBar`, `banner`, `steps`, `summary`, `header`, and `Color`/`colorize()` (raw ANSI, echo-safe) | `echo` or `writeln` both fine. |
| **Self-writing** | `spinner`, `progress`, `tasks` | They write to the output you pass them. |
| **Interactive** | `prompter`, `menu`, `keypress`, `tui` | They read input / run a loop and return values — not render strings. |

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
| [Design system](docs/design-system.md) | Theme, typography, documents, responsiveness — the big picture |
| [Architecture](docs/architecture.md) | Umbrella, sub-domains, the manager |
| [Configuration](docs/configuration.md) | Every `config/console.php` key |
| [Internationalization](docs/i18n.md) | Translating console strings |
| [Theming](docs/tools/theming.md) | Semantic palette + per-element styles (design tokens) |
| [Colours & styles](docs/tools/colors.md) | Color parsing/downgrade/gradient + the fluent Style |
| [Typography](docs/tools/typography.md) | Headings, paragraphs, lists, links, quotes, code, Text |
| [Markdown](docs/tools/markdown.md) | Render a Markdown subset to the terminal |
| [Charts](docs/tools/charts.md) | Bar, column, line, scatter, heatmap, histogram, stacked |
| [Emoji](docs/tools/emoji.md) | `:shortcode:` rendering + ASCII fallback |
| [Symbols](docs/tools/symbols.md) | Capability-aware glyphs |
| [Responsive output](docs/responsive.md) | How widgets adapt to the terminal width |
| [Interactive & live](docs/tools/interactive.md) | Live engine, animated bar, badges/pills, buttons |
| [Output formatter](docs/tools/formatting.md) | Colour/badge/link string primitives |
| [Output widgets](docs/tools/widgets.md) | Spinner, progress (+ETA), box, tree, table, gauge, summary, bar chart… |
| [Banner designer](docs/tools/banner.md) | FIGlet big-text, alignment, colour/gradient, borders |
| [Panel layout](docs/tools/panel.md) | Multi-column / nestable layout (Panel + PanelBlock) |
| [Interactive menu](docs/tools/menu.md) | Key-driven menu with a prompts fallback |
| [Full-screen TUI](docs/tools/tui.md) | symfony/tui integration — mount our widgets in a TUI app |
| [Support utilities](docs/tools/support.md) | Capabilities, Color, DisplayWidth, Emoji, Figlet, Keypress, Terminal, ANSI primitives |
| [Commands](docs/tools/commands.md) | The Artisan command base + services (or the `InteractsWithConsoleServices` trait) |
| [Runners](docs/tools/runners.md) | Conditional console execution |
| [Notifications](docs/tools/notifications.md) | The console channel |
| [Observers & events](docs/tools/observers-events.md) | Command lifecycle hooks |
| [Prompts & forms](docs/tools/prompter.md) | The Prompter, forms and validators |
| [Release process](docs/release.md) | How a version is cut and published |
| [Upgrading](UPGRADING.md) | Breaking-change migration notes (1.x → 2.0) |
| [Testing](docs/tools/testing.md) | Force capabilities + script prompts in tests |

Online docs: <https://opensource.simtabi.com/console/docs/> ·
Changelog: [CHANGELOG.md](CHANGELOG.md).

> **Requires PHP `^8.4.1`** (Laravel `^13`, Symfony 8). The core installs from
> **stable releases** — no `minimum-stability` change needed. The full-screen
> [`symfony/tui`](docs/tools/tui.md) integration is **optional**:
> `composer require symfony/tui` (experimental, needs `"minimum-stability": "dev"`)
> only if you want it.

## Stability

`laranail/console` is **2.x stable** and follows [SemVer](https://semver.org). The public
API — the `Console`/`Prompter` facades and the documented `Tools\*`/`Prompter\*`
classes — is stable; breaking changes only land in a major. Classes marked
`@internal`, and the experimental full-screen TUI (`Console::tui()` /
[`symfony/tui`](docs/tools/tui.md)), are **not** covered by the BC guarantee. See
[Versioning & stability](docs/release.md#versioning--stability).

## Local development

```bash
composer install
composer test                 # vendor/bin/pest --no-coverage (composer test-coverage for coverage)
composer lint                 # pint + phpstan + rector --dry-run
composer audit                # composer audit (security advisories)
```

## Sister packages

- [`laranail/database-tools`](https://github.com/laranail/database-tools) — standalone Laravel database utilities (traits, casts, schema macros, backup).
- [`laranail/package-tools`](https://github.com/laranail/package-tools) — runtime base library for building Laravel packages.
- [`laranail/package-scaffolder`](https://github.com/laranail/package-scaffolder) — generator that scaffolds new packages.
- [`laranail/laranail`](https://github.com/laranail/laranail) — Simtabi's Laravel utility toolbox.

## Roadmap & community

- [ROADMAP.md](ROADMAP.md) — direction at a glance (community-driven, no dates).
- [Discussions](https://github.com/laranail/console/discussions) — ideas, questions, proposals.
- [Issues](https://github.com/laranail/console/issues) — bug reports.

## Contributing & security

- [CONTRIBUTING.md](CONTRIBUTING.md) — development guidelines and PR expectations.
- [SECURITY.md](SECURITY.md) — how to report a vulnerability (opensource@simtabi.com).
- [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) — community expectations.

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
