# Installation

```bash
composer require laranail/console
```

## Requirements

- PHP `^8.4.1` (8.4.1+, incl. 8.5)
- Laravel `^13.0` (Symfony 8 components)
- `ext-mbstring`

> The package installs **stably** — no dev-stability dependency. The optional,
> **experimental** [`symfony/tui`](tools/tui.md) integration is *not* required;
> install it (`composer require symfony/tui`, PHP ≥ 8.4.1, `minimum-stability: dev`)
> only if you want the full-screen TUI. Everything else works without it.

## Service provider

`ConsoleServiceProvider` is auto-discovered via `extra.laravel.providers` — no
manual registration needed. It loads the package config and translations, binds
the `ConsoleManager` (the `Console` facade), and registers the per-sub-domain
child providers. The `Console` and `Prompter` facades and the global
`prompter()` helper are registered automatically too.

## Publishing config & translations

```bash
php artisan vendor:publish --tag=console-config
php artisan vendor:publish --tag=console-lang
```

See [Configuration](configuration.md) for every `config/console.php` key.

## Verify

`status()` returns Symfony Console markup, so write it through an output (inside a
command, use `$this->line(...)`):

```php
use Simtabi\Laranail\Console\Facades\Console;
use Symfony\Component\Console\Output\ConsoleOutput;

(new ConsoleOutput)->writeln(Console::status()->success('console toolkit installed'));
```

---

[← Docs index](../README.md#documentation)
