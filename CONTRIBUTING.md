# Contributing

Thank you for your interest in `laranail/console`.

## Quick start

```bash
git clone https://github.com/laranail/console.git
cd console
composer install
composer test
```

## Development workflow

1. Branch off `main`.
2. Write tests first when adding/changing behaviour. We aim for ≥80% line coverage on new code.
3. Run the full local check before opening a PR:
   ```bash
   composer lint          # pint + phpstan + rector --dry-run
   composer test          # vendor/bin/pest --no-coverage
   composer test-coverage # add a coverage report (needs Xdebug/PCOV)
   composer audit         # composer audit (security)
   composer bench         # phpbench micro-benchmarks (local only — noisy in CI)
   ```
4. Use [Conventional Commits](https://www.conventionalcommits.org/) — the release workflow regenerates `CHANGELOG.md` from them.
5. Open the PR against `main`. CI must pass before merge.

## Coding standards

- PHP `^8.4.1` (8.4.1+, incl. 8.5). Rector is pinned to the `php84` set; CI runs on 8.4 and 8.5.
- `declare(strict_types=1);` on every PHP file.
- `#[\Override]` on every overriding method.
- Pint is the sole formatter (see `pint.json`).
- PHPStan level 8 (see `phpstan.neon`); `composer lint` must be clean.
- Rector dry-run must be clean (see `rector.php`).

## Public-API conventions (locked at 1.0)

Since 1.0 the public API follows SemVer (see [docs/release.md](docs/release.md#versioning--stability)).
Keep new components consistent with the family so the surface stays uniform:

- **Fluent setters return `self`** (the established convention). The one exception
  is `Pill`, which `extends Badge` and so returns `static` — keep subclass-friendly
  inheritance chains returning `static`, standalone widgets returning `self`.
- Every widget/typography component exposes a static **`make()`** factory.
- **Setter parameter names are part of the BC contract** (PHP named arguments).
  Use `$width` / `$height` for size setters. The chart widgets deliberately use
  `height(int $rows)` (the value is a row count) — keep that consistent across all
  charts; don't reintroduce other size-param names elsewhere.
- Pure block components implement **`Renderable`** (`renderLines()`, get
  `render()`/`__toString()` via the internal `RendersBlock` trait); side-effecting
  ones implement **`Interactive`** and must degrade to a single static render when
  the terminal isn't interactive.
- Mark implementation-only classes **`@internal`**; they're excluded from BC.

## Artisan command naming

Commands across the laranail family follow one shape:

```
laranail::<package-slug>.<command>
```

Extend `Simtabi\Laranail\PackageTools\Commands\Command` (or `use` the
`Commands\Concerns\SupportsNamespacedNames` trait) on a command to bypass
Symfony's `::` rejection in `Command::validateName()`.

## Code of conduct

By contributing, you agree to abide by the project's
[Code of Conduct](CODE_OF_CONDUCT.md).
