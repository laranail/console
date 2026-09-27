# Changelog

All notable changes to `laranail/console` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **A fluent API on `ConsoleUIFormatter`.** You can now build a whole style as one chain:

  ```php
  ConsoleUIFormatter::create()->txtColorRed()->bgColorWhite()->bold()
      ->message('✓ Deployed 🚀')->padding(2)->lineHeight(3)
      ->addSpaceBefore()->addSpaceAfter(1, ConsoleUIFormatter::TAB);
  ```

  - **Colours:** `txtColor<Name>()` and `bgColor<Name>()` for 38 named colours, each declared
    as an `@method`. `fg()` and `bg()` take any colour `Support\Color` parses.
  - **Text:** `message()` resolves emoji shortcodes through `Support\Emoji`, which uses
    `laranail/emojis` when it is installed. `icon()` adds an emoji in front of the message.
    `bold()`, `underline()`, `blink()`, `reverse()`, `conceal()` and `href()` set the style.
  - **Layout:** `padding()` adds space inside the background, and `lineHeight()` adds blank
    background lines above and below. `addSpaceBefore()` and `addSpaceAfter()` add spaces or
    tabs outside it; each count must be positive, and repeated calls add up.
  - **Output:** `write()` renders straight to an output, and `toAnsi()` returns raw ANSI.
  - **Control:** `markup()` lets tags inside the message act as markup, and `capabilities()`
    pins emoji and colour support.

  The existing methods are unchanged, and a plain message renders exactly as before. See
  `docs/tools/formatting.md` and `examples/tools/formatting.php`.

### Fixed

- **The 14 `BRIGHT_*` and `BG_BRIGHT_*` constants threw when written.** The colour sanitiser
  stripped the hyphen, so `bright-red` reached Symfony as `brightred`.
- **`colorize()` emitted no colour for the `BRIGHT_*` constants**, because it looked them up
  as `bright-red` in a map keyed `bright_red`.
- **Colour names that `Support\Color` accepts** (`orange`, `slate`, `crimson`, …) threw when
  written. They are now converted to hex when set. An unknown colour throws
  `InvalidColorException` at the call that sets it, not later when the string is written.
- **An unknown text style**, such as `italic`, which Symfony does not support, now throws when
  it is set rather than when the string is written.
- **A clickable text colour lost its colour.** The link path returned before adding `fg`/`bg`.

### Changed

- **Message text is escaped.** A message containing `</>` used to end the style early, and
  `<fg=red>` inside it took effect. Both now print literally. Call `markup()` to keep the old
  behaviour for trusted markup.

## [0.1.3] - 2026-09-26

### Added

- **`Gauge::color()` and `Gauge::status()`** colour the filled segment, so a progress bar can read
  the same as the `StatusBadge` beside it. Output stays plain text unless one of them is called,
  so existing callers that echo a gauge are unaffected.

## [0.1.2] - 2026-09-26

### Added

- **Optional `laranail/emojis` integration** (suggested, never required). When it is installed,
  `Console::emoji()` resolves every Unicode shortcode rather than only its built-in map, and an
  ASCII-mode `render()` converts literal emoji as well as `:shortcodes:`. Without the package
  nothing changes. Console's own map still wins for its names, since several differ from the
  catalogue's (`cross` is ❌ here, ✝️ there).

  The dependency points one way: console owns the new `Tools\Contracts\EmojiCatalogue` contract,
  `laranail/emojis` ships the implementation, and console discovers it by name. Console never
  references an emojis class.

### Fixed

- **`DisplayWidth` measured emoji sequences by their parts.** A ZWJ family counted as 8 columns and
  ℹ️ as 1, pushing every border after them out of line, and `truncate()`/`truncateAnsi()` could cut
  a sequence in half. Width is now measured per grapheme cluster, with any cluster carrying ZWJ,
  VS16, a keycap, regional indicators, a skin tone or tag characters counted as two columns —
  with or without `laranail/emojis`. Measured against all 3972 emoji in Unicode 18: 3956 agree
  without the package (Symfony's width alone: 1644), and all 3972 with it. Text without such
  characters takes the old path unchanged; the added byte scan costs about 0.03 µs per call, and
  `truncateAnsi()` is about 8% faster than before.

- **The docs named the pre-namespacing publish tags, config keys and paths** —
  `--tag=console-config`, `config('console.*')`, `config/console.php` — across eleven pages and the
  README, and `docs/i18n.md` told readers to put overrides in `lang/fr/console.php`, which the
  namespaced loader never reads. All now match what the provider registers, and
  `DocumentedNamesTest` checks every documented tag, key and path against the booted application.

## [0.1.1] - 2026-09-26

### Added

- **A shared status vocabulary, `Tools\Support\Status`.** It has nine cases (`Success`, `Failed`,
  `Warning`, `Pending`, `Running`, `Skipped`, `Active`, `Inactive`, `Unknown`). Each case carries its
  glyph (Unicode or ASCII, via `Symbols`), colour, palette role and a translated label under
  `laranail-console::console.status.*`. `TaskStatus::toStatus()` maps task states onto it.
- **`StatusBadge`**: an inline status label (`✓ Completed`) built from a `Status` or a boolean.
  `StatusBadge::fromMap()` maps a domain value onto the vocabulary, and an unmapped value renders as
  `Unknown` instead of throwing. Pass `valueAsLabel: true` to show the domain value itself
  (`✓ up-to-date`).
- **`CheckList`**: an aligned readiness checklist (`✓ Tables: OK`, `✗ Seeded: NOT READY`) with a
  `passes()` summary.
- **`MetricTable`**: the two-column `Metric | Value` table, with translated headers and grouped
  integers. `metrics()` also takes a list of `[label, value]` pairs, so two rows with identical
  labels are both kept.
- **`Support\ExceptionRenderer`**: prints the message always, the file and line from `-v`, and the
  stack trace only from `-vvv`.
- **`Commands\Concerns\ConfirmsDestructiveActions`**: `confirmDestructive()` answers yes under
  `--force` without prompting, and otherwise defaults to no. The yes and no labels can be
  overridden for each action. `cancelled()` reports the refusal and
  returns a success exit code.
- Facade accessors `Console::statusBadge()`, `Console::checkList()` and `Console::metricTable()`.

### Changed

- **`Table::render($output)` renders with the target output's decoration.** It used an
  undecorated buffer, so markup inside a cell (a coloured status badge) lost its colour even on a
  TTY. Piped output is still plain.
- **`handleException()` now delegates to `ExceptionRenderer`.** The verbosity policy is unchanged.
  Exception messages are now sanitised before they are written, so an escape sequence carried inside
  a message can no longer drive the terminal.
- **A command's `$commandAliases` is read defensively** by both the `Command` base and
  `SupportsNamespacedNames`, rather than being declared on the base. A consuming command can declare
  its own list without a composition fatal, and a command that declares none no longer throws
  `Undefined property` at construction.

## [0.1.0] - 2026-08-15

### Changed

- **The config key is `laranail.console`,** published to `config/laranail/console.php`. Every read
  moves with it — `config('console.theme.preset')` is now
  `config('laranail.console.theme.preset')`. Laravel's config repository is a flat map and `console`
  is a name an application could plausibly use for its own file.

- **Publish tags are vendor-scoped:** `console-config` → `laranail::console-config`, `console-lang`
  → `laranail::console-lang`.

### Fixed

- **Published translations went to the lang root** rather than to
  `lang/vendor/laranail-console`, which is where the namespaced loader looks — so every published
  override was silently ignored while the packaged default kept answering.

Initial public release.
