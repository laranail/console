# Architecture

`laranail/console` is an umbrella package with two decoupled sub-domains under a
single namespace root, `Simtabi\Laranail\Console`:

```
Console
├── ConsoleManager          # thin aggregator: ui(), prompter(), spinner()…
├── Facades\Console          # facade over ConsoleManager
├── Providers\ConsoleServiceProvider   # config + lang + registers child providers
├── Exceptions\ConsoleException        # base, fromKey() with safe fallback
├── Tools\                   # OUTPUT
│   ├── Formatting\          # ConsoleUIFormatter (colour/badge/link primitives)
│   ├── Widgets\             # Spinner, ProgressBar, Box, Tree, Table, TaskProgress,
│   │                        #   Summary, Header, Banner, Panel/PanelBlock, Menu\…
│   ├── Contracts\           # Renderable (panel composition)
│   ├── Support\             # Capabilities, DisplayWidth, Symbols, BorderStyle, Color,
│   │                        #   Emoji, Figlet, Keypress, Terminal, Sgr/ControlChars/Csi
│   ├── Commands\            # Command (enhanced base) + Services\ (nine services)
│   ├── Runners\             # BaseRunner + ConsoleRunner
│   ├── Observers\, Events\  # command lifecycle hooks + CommandEvents
│   └── Notifications\       # ConsoleChannel (+ contract)
└── Prompter\                # INPUT
    ├── Prompter, Services\  # fluent wrapper + PromptService + FormBuilder
    ├── Validators\          # 25+ validators (+ AbstractValidator, LaravelRule)
    ├── Enums\, Facades\, Helpers\, Support\
    └── Exceptions\          # PrompterException (extends ConsoleException)
```

## The aggregator

`ConsoleManager` (bound as a singleton, fronted by the `Console` facade) is the
**only** place the two sub-domains meet. Its accessors return the real domain
objects — `ui()` → `ConsoleUIFormatter`, `prompter()` → `Prompter`, plus the
widget factories — so it never proxies methods. `Tools\*` and `Prompter\*` never
import one another, which keeps each independently testable and re-splittable.

## The Support backbone

Every renderer routes through `Tools\Support`: terminal capability detection
(`Capabilities`), ANSI/wide-char-aware width (`DisplayWidth`), one glyph map
(`Symbols`), box-drawing families (`BorderStyle`) and colour (`Color`). This is
why Unicode/colour degrade consistently and aligned output never drifts.

## Dependencies

Only `illuminate/console`, `illuminate/support`, `illuminate/contracts`,
`laravel/prompts` and `symfony/console` — no heavyweight additions, no
dependency on the laranail core package.

---

[← Docs index](../README.md#documentation)
