# Console notification channel

`Simtabi\Laranail\ConsoleTools\Notifications\ConsoleChannel` is a
self-contained console output channel (implements the local
`Notifications\Contracts\ConsoleChannelInterface`). It writes timestamped,
optionally data-annotated messages to a Symfony console output, or `echo`s
when no output is supplied.

```php
use Simtabi\Laranail\ConsoleTools\Notifications\ConsoleChannel;

$channel = new ConsoleChannel(config: ['show_data' => true], output: $output);
$channel->send('Deployment finished', ['level' => 'info', 'duration' => '12s']);
// [2026-06-17 10:00:00] Deployment finished | Data: {"level":"info","duration":"12s"}
```

It is intentionally **decoupled** from any host notification framework — it
depends on nothing but Symfony console. If you need it to participate in a
specific notification system, wrap it in a thin adapter implementing that
system's channel contract.

---

[← Docs index](../README.md#documentation)
