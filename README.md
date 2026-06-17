# laranail/console-tools

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

| Page | What's inside |
|------|---------------|
| [Installation](docs/installation.md) | Requirements, install, auto-discovery |
| [Architecture](docs/architecture.md) | Component map across the package |
| [Formatting](docs/formatting.md) | `ConsoleUIFormatter` + `ConsoleProgressBar` |
| [Commands](docs/commands.md) | `LaranailCommand` base + the nine command services |
| [Observers & events](docs/observers-events.md) | `ConsoleCommandObserver`, `CommandEvents` |
| [Runners](docs/runners.md) | `ConsoleRunner` / `BaseRunner` conditional execution |
| [Notifications](docs/notifications.md) | Standalone console output channel |
| [Configuration](docs/configuration.md) | Optional configuration knobs |

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
