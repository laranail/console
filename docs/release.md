# Release process

`laranail/console` is released **tag-driven**: pushing a `vX.Y.Z` tag triggers the
GitHub release workflow, which publishes the GitHub Release. Packagist updates from
the tag automatically.

## Versioning & stability

As of **1.0**, the package follows [Semantic Versioning](https://semver.org). A
release that only fixes bugs is a patch (`x.y.Z`); new backward-compatible features
are a minor (`x.Y.0`); a breaking change is a major. The PHP floor (`^8.4.1`) and
Laravel/Symfony constraints live in `composer.json`.

**What SemVer covers (the public API):**

- The `Console` and `Prompter` facades and the domain objects they return.
- The documented `Tools\*` and `Prompter\*` classes — everything described under
  [`docs/`](../README.md#documentation): widgets, typography, the document/Markdown
  layer, charts, `Support\*` (Color, Capabilities, DisplayWidth, Symbols, Figlet,
  BrailleCanvas, …), command base + services, runners, observers, notifications,
  validators, the `Renderable` / `Interactive` contracts, the command traits
  (`InteractsWithConsoleServices`, `SupportsNamespacedNames`), and the testing trait.

**What is NOT covered:**

- Anything marked **`@internal`** (service providers, internal traits/helpers).
- The **experimental** full-screen TUI integration — `Console\Tui\*` and
  `Console::tui()` — which rides the upstream `symfony/tui` `@dev` package and may
  change or break with it at any time.

Breaking changes to the public API only happen in a major release, with the change
recorded in the [CHANGELOG](../CHANGELOG.md) (and, from the first 2.0, an
`UPGRADING.md`).

## Cutting a release

1. Work on a branch (`feat/vX.Y.Z`), one logical change per commit.
2. Keep every gate green per commit:
   ```bash
   composer lint     # pint + phpstan (level 8) + rector --dry-run
   composer test     # pest --no-coverage
   composer examples # every examples/ script must exit 0
   composer audit    # no advisories
   ```
3. Add a dated `## [X.Y.Z] - YYYY-MM-DD` section to [`CHANGELOG.md`](../CHANGELOG.md)
   (Keep a Changelog format: Added / Changed / Fixed / Removed).
4. Open a PR; CI runs the 8.4 / 8.5 × prefer-lowest/stable matrix plus
   static-analysis, security and audit jobs. Merge when green.
5. Tag and push:
   ```bash
   git tag -a vX.Y.Z -m "vX.Y.Z — summary"
   git push origin vX.Y.Z
   ```
6. The release workflow publishes the GitHub Release. Verify it, then prune merged
   branches (`git remote prune origin`).

## Conventions

- Commits/PRs carry no AI attribution; author identity is the maintainer's.
- Never rewrite published history or re-tag a released version — consumers and
  caches depend on the existing tags.
- GitHub Actions are pinned by SHA and kept current by Dependabot.

[← Docs index](../README.md#documentation)
