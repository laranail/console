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
│   ├── Contracts\           # Renderable, Interactive (panel composition / live)
│   ├── Support\             # Capabilities, DisplayWidth, Symbols, BorderStyle, Color,
│   │                        #   Emoji, Figlet, Fonts\ (Block/Builtin/FontDefinition),
│   │                        #   BrailleCanvas, Hyperlink, Keypress, Terminal, Sgr/ControlChars/Csi
│   ├── Commands\            # Command base + Concerns\InteractsWithConsoleServices trait + Services\ (nine + manager)
│   ├── Runners\             # BaseRunner + ConsoleRunner
│   ├── Observers\, Events\  # command lifecycle hooks + CommandEvents
│   └── Notifications\       # ConsoleChannel (+ contract)
└── Prompter\                # INPUT
    ├── Prompter, Services\  # fluent wrapper + PromptService + FormBuilder
    ├── Validators\          # 26 validators (incl. LaravelRule) + AbstractValidator/RegexValidator/DateTimeFormatValidator/ChoiceFieldValidator bases
    │                        #   (2.0: domain-only constructors; message/replace/locale set fluently)
    ├── Commands\, Contracts\, Enums\, Facades\, Helpers\, Providers\
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

Runtime: `illuminate/console`, `illuminate/support`, `illuminate/contracts`,
`laravel/prompts`, `symfony/console` (^8). The PHP `^8.4.1` floor comes from
`composer.json` `require.php` directly.

`symfony/tui` (+ `symfony/event-dispatcher`, `symfony/string`, `revolt/event-loop`)
is **optional** — `require-dev` + `suggest`, experimental — and only needed for the
full-screen [TUI integration](tools/tui.md). No dependency on the laranail core package.

## Public surface

Since 1.0 the public, SemVer-covered API is marked `@api` (the `Console`/`Prompter`
facades, the `Renderable`/`Interactive` contracts, the `Command` base + the
`InteractsWithConsoleServices` / `SupportsNamespacedNames` traits). Implementation-only classes are marked
`@internal` (service providers, `RendersBlock`, `ResponsiveWidth`, the Prompter
`ContextBuilderService`/`Helpers`) and are excluded from BC, as is the experimental
TUI integration. See [Versioning & stability](release.md#versioning--stability).

---

[← Docs index](../README.md#documentation)
