#!/usr/bin/env bash
# .scripts/init.sh — single bootstrap entrypoint for laranail/console-tools.
# The only shell script in the repo; everything else runs through Composer.
#
# Behavior (idempotent, exits non-zero on any check failure):
#   1. Verify php >= 8.3 and composer are on PATH.
#   2. composer install (or --no-dev when INIT_PROD=1).
#   3. Smoke-check lint (non-fatal warnings).
#   4. Print summary.

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

err()  { printf '\033[31m✗\033[0m %s\n' "$*" >&2; }
ok()   { printf '\033[32m✓\033[0m %s\n'   "$*"; }
info() { printf '\033[34mi\033[0m %s\n'   "$*"; }
warn() { printf '\033[33m!\033[0m %s\n'   "$*"; }

check_only=false
for arg in "$@"; do
    case "$arg" in
        --check-only) check_only=true ;;
    esac
done

command -v php >/dev/null      || { err "php not found on PATH"; exit 2; }
command -v composer >/dev/null || { err "composer not found on PATH"; exit 2; }

php_version=$(php -r 'echo PHP_VERSION;')
php_major_minor=$(printf '%s' "$php_version" | cut -d. -f1-2)
if [ "$(printf '%s\n8.3' "$php_major_minor" | sort -V | head -1)" != "8.3" ]; then
    err "PHP 8.3+ required (found $php_version)"
    exit 2
fi
ok "php $php_version"
ok "composer $(composer --version | sed 's/^Composer version //;s/ .*$//')"

if [ "$check_only" != "true" ]; then
    if [ "${INIT_PROD:-0}" = "1" ]; then
        info "composer install --no-dev"
        composer install --no-dev --no-interaction --prefer-dist
    else
        info "composer install"
        composer install --no-interaction --prefer-dist
    fi
    ok "composer install complete"
fi

if [ "$check_only" != "true" ] && [ -f vendor/bin/pint ]; then
    if vendor/bin/pint --test --quiet 2>/dev/null; then
        ok "pint clean"
    else
        warn "pint reports formatting drift (run 'composer pint-fix')"
    fi
fi

printf '\n──────────────────────────────────────────\n'
printf '\033[1mlaranail/console-tools\033[0m setup complete\n\n'
printf 'Available composer aliases:\n'
printf '  composer test           — run Pest tests\n'
printf '  composer lint           — pint + phpstan + rector --dry-run\n'
printf '  composer audit          — composer audit (security)\n'
printf '  composer pint-fix       — apply Pint fixes\n\n'
printf 'Docs: https://opensource.simtabi.com/console-tools/docs/\n'
printf '──────────────────────────────────────────\n'
