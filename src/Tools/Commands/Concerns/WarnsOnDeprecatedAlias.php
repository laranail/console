<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Commands\Concerns;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * Keeps a command's old names working as DEPRECATED aliases that say so when used.
 *
 * A command lists them in its own `$deprecatedCommandAliases`:
 *
 * ```php
 * protected $signature = 'laranail::toolkit.make-crud';
 *
 * protected array $deprecatedCommandAliases = ['make:crud'];
 * ```
 *
 * Each one is registered as an alias -- so every existing script, scheduler entry and
 * `Artisan::call()` keeps working -- and invoking the command by it prints one line naming the
 * canonical command before anything else runs. Invoking it by its name, or by an alias in
 * `$commandAliases`, prints nothing.
 *
 * This is the one sanctioned way for a bare generic name (`make:crud`, `module:make`) to stay
 * registered beside a vendor-scoped one: a plain alias hands back the flat-registry collision
 * the namespaced name exists to prevent, and a warning that names the replacement is what makes
 * the alias a migration path rather than a second name.
 *
 * The base {@see \Simtabi\Laranail\Console\Tools\Commands\Command} uses this trait; `use` it
 * directly on a command that extends a different base. A command that overrides `initialize()`
 * must call `parent::initialize()` or alias this trait's method, or the warning never prints.
 *
 * Detection reads the name the caller actually typed: the input's first argument is the command
 * token for `php artisan`, `Artisan::call()` and `$this->call()` alike, while `getName()` is
 * always the canonical name. Symfony calls `initialize()` after binding the input and before
 * `interact()`, so the warning prints before any prompt. On a real terminal it goes to stderr,
 * so piped output is unchanged.
 *
 * @api Stable extension point (SemVer-covered).
 */
trait WarnsOnDeprecatedAlias
{
    /**
     * Every alias, including the deprecated ones, so the application registers them all.
     *
     * @return list<string>
     */
    public function getAliases(): array
    {
        return array_values(array_unique([...parent::getAliases(), ...$this->deprecatedCommandAliases()]));
    }

    /**
     * The aliases this command keeps only for backwards compatibility.
     *
     * Read from the consuming command's own `$deprecatedCommandAliases`, for the same reason
     * `$commandAliases` is: declaring the property on a trait makes a using class that declares
     * it with a different default a fatal at composition, and reading an undeclared one throws.
     * The command's own name is never treated as deprecated.
     *
     * @return list<string>
     */
    public function deprecatedCommandAliases(): array
    {
        if (! property_exists($this, 'deprecatedCommandAliases') || ! is_array($this->deprecatedCommandAliases)) {
            return [];
        }

        $name = $this->getName();

        return array_values(array_unique(array_filter(
            $this->deprecatedCommandAliases,
            static fn (mixed $alias): bool => is_string($alias) && $alias !== '' && $alias !== $name,
        )));
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $invokedAs = $input->getFirstArgument();

        if (! is_string($invokedAs) || ! in_array($invokedAs, $this->deprecatedCommandAliases(), true)) {
            return;
        }

        $target = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $target->writeln(sprintf(
            '<comment>Deprecated:</comment> [%s] is a deprecated alias and will be removed in the next minor after 0.1. Use [%s] instead.',
            $invokedAs,
            (string) $this->getName(),
        ));
    }
}
