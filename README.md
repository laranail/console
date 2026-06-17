# laranail/console-tools

Console & terminal-UI toolkit for Laravel packages — a fluent output
formatter, progress bars, an enhanced Artisan command base with a
service-based architecture, and database-seeding output formatting.

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
echo ConsoleUIFormatter::statusLine('UserSeeder', 'DONE', '12.30', true);
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
| [Architecture](docs/architecture.md) | Component map: formatting, commands, services |
| [Configuration](docs/configuration.md) | Optional configuration knobs |

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
