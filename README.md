# laranail/console

[![Tests](https://github.com/laranail/console/actions/workflows/tests.yml/badge.svg)](https://github.com/laranail/console/actions/workflows/tests.yml)
[![Static analysis](https://github.com/laranail/console/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/laranail/console/actions/workflows/static-analysis.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

`laranail/console` is not published to Packagist, so there is no registry-version badge to show: see [Install](#install).

> A Rich-class console toolkit for Laravel — fluent terminal **output** (formatter, spinners, progress bars, boxes, trees, tables, charts, a Markdown + typography layer) and **input** (a `laravel/prompts` wrapper with a form builder and 26 validators).

Requires PHP `^8.4.1` on Laravel `^13` (Symfony 8).

## Install

```bash
composer require laranail/console
```

The service provider, the `Console`/`Prompter` facades, and the `prompter()` helper are auto-discovered.
Publish the config and language files if you want to customise them:

```bash
php artisan vendor:publish --tag=laranail::console-config
php artisan vendor:publish --tag=laranail::console-lang
```

## Quick start guide and usage

### Getting started

Nothing to configure: `ConsoleServiceProvider` registers itself through package discovery,
together with the `Console` and `Prompter` facades and the `prompter()` helper. The config and
language publishes above are only for customising them.

### Usage

```php
use Simtabi\Laranail\Console\Facades\Console;
use Simtabi\Laranail\Console\Tools\Commands\Command;

final class ReleaseSummaryCommand extends Command
{
    protected $signature = 'release:summary';

    public function handle(): int
    {
        $this->output->writeln(Console::status()->success('Build complete'));
        echo Console::box(['Version: 2.4.0', 'Env:     production'])->title('Release')->render();

        return self::SUCCESS;
    }
}
```

Progress and a prompt:

```php
Console::spinner('Compiling…')->run(fn () => compile());

$name = Console::prompter()->text('Your name', required: true)->getResult();
```

The full walkthrough is in [Getting started](docs/getting-started.md); everything else is in the [documentation index](#documentation).

## Documentation

Full documentation is at **[opensource.simtabi.com/documentation/laranail/console](https://opensource.simtabi.com/documentation/laranail/console/)** — installation, getting started, the design system, architecture, configuration, and per-subsystem reference (theming, colours, typography, Markdown, charts, widgets, banners, panels, menus, the full-screen TUI, prompts & forms, and more).

## Contributing & security

Issues and PRs are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Report vulnerabilities per
[SECURITY.md](SECURITY.md) (opensource@simtabi.com); participation follows the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
