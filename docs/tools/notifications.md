# Console notification channel

`Simtabi\Laranail\Console\Tools\Notifications\ConsoleChannel` is a
self-contained console output channel (implements the local
`Notifications\Contracts\ConsoleChannelInterface`). It writes timestamped,
optionally data-annotated messages to a Symfony console output, or `echo`s
when no output is supplied.

```php
use Simtabi\Laranail\Console\Tools\Notifications\ConsoleChannel;

$channel = new ConsoleChannel(config: ['show_data' => true], output: $output);
$channel->send('Deployment finished', ['level' => 'info', 'duration' => '12s']);
// [2026-06-17 10:00:00] Deployment finished | Data: {"level":"info","duration":"12s"}
```

It is intentionally **decoupled** from any host notification framework — it
depends on nothing but Symfony console. If you need it to participate in a
specific notification system, wrap it in a thin adapter implementing that
system's channel contract.

## Host-adapter pattern

To plug the writer into a host's notification system, implement the host's
channel contract and delegate `send()` to this channel. The adapter owns the
host concerns (enabled flags, config validation, naming); this channel owns
the console output. For example, `laranail/laranail` exposes a console
notification channel as a thin adapter:

```php
namespace App\Notifications\Channels;

use Simtabi\Laranail\Console\Tools\Notifications\ConsoleChannel as ConsoleWriter;
use Vendor\Notifications\Contracts\NotificationChannelInterface;

final class ConsoleChannel implements NotificationChannelInterface
{
    private ConsoleWriter $writer;

    public function __construct(array $config = [])
    {
        $this->writer = new ConsoleWriter($config);
    }

    public function getName(): string
    {
        return 'console';
    }

    public function send(string $message, array $data = []): bool
    {
        return $this->writer->send($message, $data);
    }
}
```

Because the writer takes a `Symfony\Component\Console\Output\OutputInterface`,
the adapter can also forward a buffered output for testing, or let it fall
back to `echo` in plain runtime.

---

[← Docs index](../../README.md#documentation)
