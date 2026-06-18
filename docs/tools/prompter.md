# Prompts & forms

`Console\Prompter` is a fluent layer over `laravel/prompts` with a form builder
and a library of validators.

## Prompting

```php
use Simtabi\Laranail\Console\Facades\Console;

$name  = Console::prompter()->text('Your name', required: true)->getResult();
$pass  = prompter()->password('Password')->getResult();
$pick  = prompter()->select('Driver', ['mysql' => 'MySQL', 'pgsql' => 'Postgres'])->getResult();
```

All `laravel/prompts` types are available — `text`, `textarea`, `password`,
`select`, `multiselect`, `confirm`, `suggest`, `search`, `multisearch`, `pause`,
`spin`, `progress`, `table` and `form` — plus output context via
`prompter()->context()->info(...)`.

## Forms

```php
use Simtabi\Laranail\Console\Prompter\Enums\FieldType;
use Simtabi\Laranail\Console\Prompter\Services\FormBuilder\FormFieldService;

$form = prompter()->form(); // a Laravel\Prompts\FormBuilder
```

The `FormBuilderService` maps each `FieldType` to the correct prompt method and
attaches a default validator, so choice fields validate against their options
and text-like fields against their type.

## Validators

25+ validators implement `ValidatorInterface` (`validate(mixed): ?string`):
email, phone, date, time, password, username, UUID, path, colour, name, number,
string, alpha, alphanumeric, array, object, boolean, and more. They are **total**
— non-string input returns the error message rather than throwing.

Bridge Laravel validation rules with `LaravelRule`:

```php
use Simtabi\Laranail\Console\Prompter\Validators\LaravelRule;

prompter()->text('Email', validate: new LaravelRule(['required', 'email']));
```

Validator messages live under `console::validators.*` (see
[Internationalization](../i18n.md)).

[← Docs index](../../README.md#documentation)
