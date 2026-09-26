# Changelog

All notable changes to `laranail/console` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
