# Interactive menu

`Menu` (`Simtabi\Laranail\Console\Tools\Widgets\Menu`, `Console::menu()`) is a
native interactive menu. On a TTY with raw key input it renders a navigable frame
(↑/↓ to move, space to toggle checkboxes/radios, enter to choose, q/esc to exit).
Where raw input isn't available — a non-TTY pipe, or Windows — it **transparently
falls back to [laravel/prompts](prompter.md)**, so it always works and needs no
`ext-posix`.

It mirrors the ergonomic API of
[nunomaduro/laravel-console-menu](https://github.com/nunomaduro/laravel-console-menu)
(MIT) but is an independent implementation built on our own Keypress reader — see
[THIRD_PARTY.md](../../THIRD_PARTY.md).

## In a command

A `menu()` macro is registered on `Illuminate\Console\Command`:

```php
public function handle(): int
{
    $choice = $this->menu('Pizza menu', [
        'Margherita',
        'Pepperoni',
        'Veggie',
    ])->open();

    $this->info("You chose: {$choice}");

    return self::SUCCESS;
}
```

A list of options returns the chosen **label**; an associative `value => label`
array (via `addOption`/`addOptions`) returns the chosen **value**.

## Fluent builder

```php
use Simtabi\Laranail\Console\Facades\Console;

$result = Console::menu('Main menu')
    ->addOption('deploy', 'Deploy')
    ->addOption('rollback', 'Roll back')
    ->addStaticItem('— danger zone —')
    ->addCheckbox('force', 'Force', false)
    ->addRadio('env-prod', 'Production')
    ->addRadio('env-stage', 'Staging')
    ->addQuestion('Custom command', 'e.g. cache:clear')
    ->addSubMenu('Advanced', fn ($m) => $m->addOption('gc', 'Garbage collect'))
    ->setWidth(50)
    ->setForegroundColour('#22d3ee')
    ->setExitButtonText('Quit')
    ->open();
```

| Method | Effect |
|--------|--------|
| `addOption($value, $label)` / `addOptions($array)` | a selectable value-bearing option |
| `addStaticItem($label)` / `addLineBreak()` | non-selectable label / spacer |
| `addCheckbox($value, $label, $checked=false)` | multi-select toggle |
| `addRadio($value, $label, $checked=false)` | single-select toggle |
| `addQuestion($label, $placeholder='')` | free-text answer (bridges to prompts `text()`) |
| `addSubMenu($label, fn (Menu $m) => …)` | a nested menu, opened on select |
| `setWidth` / `setPadding` / `setForegroundColour` / `setExitButtonText` | styling |
| `open(): mixed` | run the menu |

**`open()` returns**: a single value (option), the typed string (question), the
nested result (submenu), an array of values (when checkboxes/radios are present),
or `null` if exited. Advanced cli-menu features (split items, in-menu dialogues)
are out of scope for now.

## Configuration

`config/console.php`:

```php
'menu' => [
    'foreground' => env('CONSOLE_MENU_FG'),   // hex/colour or null
    'width'      => env('CONSOLE_MENU_WIDTH'), // frame width or null to auto-fit
],
```

[← Docs index](../../README.md#documentation)
