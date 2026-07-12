# Getting started

A five-minute tour: install `laranail/console`, print your first styled output, and read your first prompt.
For the full reference see the [Documentation index](../README.md#documentation).

## 1. Install

```bash
composer require laranail/console
```

The `Console` and `Prompter` facades + the `prompter()` helper are auto-discovered. Optionally publish the
config + language files:

```bash
php artisan vendor:publish --tag=console-config
php artisan vendor:publish --tag=console-lang
```

## 2. Your first output

Inside an Artisan command you have `$this->output`; anywhere else pass any Symfony `OutputInterface`.
Markup-bearing helpers go **through** the output; finished-string renderers `echo` fine:

```php
use Simtabi\Laranail\Console\Facades\Console;

$output->writeln(Console::status()->success('Build complete'));
echo Console::box(['Name: app', 'Env:  prod'])->title('Config')->render();
echo Console::tree('project')->child('src', fn ($t) => $t->child('Console'))->render();
```

Know which of the [three output styles](../README.md#writing-through-an-output) a helper returns — it
decides whether you `writeln()` or `echo`.

## 3. Progress + a prompt

```php
Console::spinner('Compiling…')->run(fn () => compile());

$name = Console::prompter()->text('Your name', required: true)->getResult();
```

## Next steps

- [Output widgets](tools/widgets.md) — spinner, progress, box, tree, table, gauge, charts…
- [Prompts & forms](tools/prompter.md) — the Prompter, forms, and validators.
- [Design system](design-system.md) — theming, typography, documents, responsiveness.
- [Configuration](configuration.md) — every `config/console.php` key.

---

[← Docs index](../README.md#documentation)
