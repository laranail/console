# laranail/console-tools

[![Latest version on Packagist](https://img.shields.io/packagist/v/laranail/console-tools.svg)](https://packagist.org/packages/laranail/console-tools)
[![Tests](https://github.com/laranail/console-tools/actions/workflows/tests.yml/badge.svg)](https://github.com/laranail/console-tools/actions/workflows/tests.yml)
[![Static analysis](https://github.com/laranail/console-tools/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/laranail/console-tools/actions/workflows/static-analysis.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Console & terminal-UI toolkit for Laravel packages — a fluent output
formatter, progress bars, and an enhanced Artisan command base with a
service-based architecture.

Extracted from the `laranail/laranail` core so any package can depend on
the console layer without pulling in the whole framework.

## Install

```bash
composer require laranail/console-tools
```

Requires PHP `^8.3` (8.3–8.5) and Laravel `^13.0`. The service provider is
auto-discovered.

## Quick start

```php
use Simtabi\Laranail\ConsoleTools\Formatting\ConsoleUIFormatter;

echo ConsoleUIFormatter::success('Done!');
echo ConsoleUIFormatter::statusLine('BuildAssets', 'DONE', '12.30', true);
```

Build commands on the enhanced base:

```php
use Simtabi\Laranail\ConsoleTools\Commands\LaranailCommand;

final class SyncCommand extends LaranailCommand
{
    protected $signature = 'app:sync';

    public function handle(): int
    {
        $this->infoMessage('Starting…');
        // performance/metadata/signal handling provided by the base
        return self::SUCCESS;
    }
}
```

## <a id="documentation"></a>Documentation

Hosted at [`opensource.simtabi.com/console-tools/docs/`](https://opensource.simtabi.com/console-tools/docs/)
(product page: [`opensource.simtabi.com/console-tools/`](https://opensource.simtabi.com/console-tools/)).
The same pages live under [`docs/`](docs/):

**Guides**

- [Installation](docs/installation.md) — requirements, install, auto-discovery
- [Architecture](docs/architecture.md) — component map across the package
- [Configuration](docs/configuration.md) — the optional configuration knobs

**Tools & features**

- [Formatting](docs/tools/formatting.md) — `ConsoleUIFormatter` + `ConsoleProgressBar`
- [Commands](docs/tools/commands.md) — the `LaranailCommand` base and its nine command services
- [Runners](docs/tools/runners.md) — `ConsoleRunner` / `BaseRunner` conditional execution
- [Observers & events](docs/tools/observers-events.md) — `ConsoleCommandObserver`, `CommandEvents`
- [Notifications](docs/tools/notifications.md) — standalone console output channel

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
