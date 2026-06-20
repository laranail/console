# Prompts, forms & validators

`Console\Prompter` is a fluent layer over `laravel/prompts` with a form builder and
a library of 26 validators.

## Prompting

```php
use Simtabi\Laranail\Console\Facades\Console;

$name = Console::prompter()->text('Your name', required: true)->getResult();
$pass = prompter()->password('Password')->getResult();
$pick = prompter()->select('Driver', ['mysql' => 'MySQL', 'pgsql' => 'Postgres'])->getResult();
```

Each call returns the `Prompter` (fluent); the entered value is read with
`getResult()`. `Console::prompter()`, the global `prompter()` helper and resolving
`Prompter` from the container each return a **fresh instance**, so one chain's
`getResult()` never clobbers another's.

**Every `laravel/prompts` helper is available** — the wrapper forwards any unknown
method to the matching `Laravel\Prompts\{name}()` function, so it auto-tracks the
full prompts API (current and future) without per-method maintenance:

`text`, `textarea`, `password`, `number`, `confirm`, `select`, `multiselect`,
`suggest`, `search`, `multisearch`, `autocomplete`, `pause`, `clear`, `spin`,
`progress`, `task`, `table`, `datatable`, `grid`, `stream`, `form`, and the
context helpers `note`, `info`, `warning`, `error`, `alert`, `intro`, `outro`,
`title`, `notify`. An unknown method throws `PrompterException`. Requires
`laravel/prompts` `^0.3.18 || ^1.0`.

### Context output

The context helpers are reachable directly on the prompter or via `->context()`:

```php
prompter()->note('Saved.');           // also: error, warning, alert, info, intro, outro, title
prompter()->context()->warning('Heads up');
prompter()->number('Port', default: 8080)->getResult();   // newer prompts helpers too
```

## Forms

`prompter()->form()` returns a raw `Laravel\Prompts\FormBuilder`. For richer,
validated fields use the package's `FormBuilderService` + `FormFieldService`:

```php
use Laravel\Prompts\FormBuilder;
use Simtabi\Laranail\Console\Prompter\Enums\FieldType;
use Simtabi\Laranail\Console\Prompter\Services\FormBuilder\FormBuilderService;
use Simtabi\Laranail\Console\Prompter\Services\FormBuilder\FormFieldService;

$form = new FormBuilderService(new FormBuilder());

$form->addField('email', (new FormFieldService(FieldType::EMAIL))
    ->label('Email')->required(true));

$form->addField('driver', (new FormFieldService(FieldType::SELECT))
    ->label('Driver')->options(['mysql' => 'MySQL', 'pgsql' => 'Postgres']));

$answers = $form->build()->submit(); // ['email' => ..., 'driver' => ...]
```

`FormFieldService` is fully chainable: `label()`, `placeholder()`, `required()`,
`hint()`, `default()`, `options()`, `validator()`, `customValidator()`,
`customErrorMessage()`. Each `FieldType` maps to a real `FormBuilder` method and
gets an options-aware default validator unless you set your own.

### Field types

`FieldType` cases: `TEXT`, `NUMBER`, `EMAIL`, `PASSWORD`, `TEXTAREA`, `DATE`,
`TIME`, `SELECT`, `CHECKBOX`, `RADIO`, `PATH`, `USERNAME`, `PHONE`, `COLOR`,
`NULL_OR_EMPTY`, `ARRAY`, `OBJECT`, `UUID`, `ALPHA`, `ALPHANUMERIC`,
`UUID_OR_INTEGER_OR_SLUG`, `BOOLEAN`, `NAME`, `STRING`, `JSON`.

## Validators

Each implements `ValidatorInterface::validate(mixed): ?string` (null = valid,
otherwise the error message). All are **total** — non-string input returns the
error rather than throwing. Every constructor ends with the common tail
`(?string $errorMessage = null, array $replace = [], ?string $locale = null)`;
messages default to `console::validators.*` (see [i18n](../i18n.md)).

| Validator | Notable constructor args | Accepts |
|-----------|--------------------------|---------|
| `TextFieldValidator` | — | string ≤ 255 chars |
| `TextAreaFieldValidator` | — | any string |
| `StringFieldValidator` | `int $minLength = 0, int $maxLength = 255` | string within length |
| `NumberFieldValidator` | — | numeric |
| `EmailFieldValidator` | — | valid email |
| `PasswordFieldValidator` | — | string ≥ 8 chars |
| `NameFieldValidator` | — | letters/spaces/`'`/`-` |
| `UsernameValidator` | — | `[A-Za-z0-9_]{3,20}` |
| `PhoneNumberValidator` | — | `+?[0-9]{10,15}` |
| `ColorValidator` | — | hex / rgb / rgba |
| `DateFieldValidator` | `?array $formats = null` | a date in the given formats |
| `TimeFieldValidator` | `?array $formats = null` | a time in the given formats |
| `PathFieldValidator` | — | path shape (no `..`/null byte) |
| `UUIDFieldValidator` | — | RFC-4122 UUID (any version) |
| `UuidOrIntegerOrSlugValidator` | `string $uuidVersion = 'uuid'` | UUID, integer id, or slug |
| `AlphaValidator` | — | `[A-Za-z]+` |
| `AlphanumericValidator` | — | `[A-Za-z0-9]+` |
| `BooleanFieldValidator` | — | bool / yes/no / 1/0 |
| `CheckboxFieldValidator` | — | bool |
| `SelectFieldValidator` | `array $options` (first) | a value in `$options` |
| `RadioFieldValidator` | `array $options` (first) | a value in `$options` |
| `ArrayValidator` | — | array |
| `ObjectValidator` | — | object |
| `JsonFieldValidator` | — | valid JSON string |
| `NullOrEmptyValidator` | — | null or `''` |
| `LaravelRule` | `array\|string $rules` (first) | anything passing the Laravel validation rules |

The regex-pattern validators (`Alpha`, `Alphanumeric`, `Name`, `Username`,
`PhoneNumber`, `UUID`) share an abstract `RegexValidator` base — extend it to add
your own single-pattern validator.

The choice validators take `$options` **first**:

```php
use Simtabi\Laranail\Console\Prompter\Validators\SelectFieldValidator;

new SelectFieldValidator(['mysql', 'pgsql'], 'Pick a supported driver');
```

### Laravel rule bridge

Reuse Illuminate validation rules in a prompt:

```php
use Simtabi\Laranail\Console\Prompter\Validators\LaravelRule;

prompter()->text('Email', validate: new LaravelRule(['required', 'email']));
// optional custom messages / override:
new LaravelRule(['email'], ['email' => 'Bad address']);
new LaravelRule(['email'], errorMessage: 'Invalid');
```

[← Docs index](../../README.md#documentation)
