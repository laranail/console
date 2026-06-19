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
   composer lint     # pint + phpstan + rector --dry-run
   composer test     # vendor/bin/pest
   composer audit    # composer audit (security)
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
