<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Commands\Concerns;

use ReflectionProperty;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * Lets a command use the laranail naming shape `laranail::<package-slug>.<command>`.
 *
 * Symfony's {@see SymfonyCommand::validateName()} rejects the empty segment in `::`,
 * so this trait sets the name (and aliases) past that validator by writing the
 * private property directly. Dispatch still works because Symfony resolves an exact
 * command name before its `:`-splitting namespace lookup.
 *
 * @api Stable extension point (SemVer-covered).
 */
trait SupportsNamespacedNames
{
    public function setName(string $name): static
    {
        $this->writeName('name', $name);

        return $this;
    }

    /**
     * @param list<string> $aliases
     */
    public function setAliases(iterable $aliases): static
    {
        $this->writeName('aliases', is_array($aliases) ? $aliases : iterator_to_array($aliases));

        return $this;
    }

    /**
     * The consuming command's own `$commandAliases`, if it declares one.
     *
     * Deliberately NOT a property on this trait. PHP rejects a trait and a using
     * class declaring the same property with different defaults -- a fatal at
     * composition -- so declaring it here makes the documented usage ("a command
     * declares its own list") impossible to write. Reading it defensively covers
     * both that and the opposite bug, where an undeclared property threw
     * `Undefined property` at construction.
     *
     * An alias must itself be vendor-scoped. A bare `env:set` beside
     * `laranail::env-kit.set` hands back exactly the flat-registry collision the
     * namespaced name exists to prevent.
     *
     * @return list<string>
     */
    private function declaredCommandAliases(): array
    {
        if (! property_exists($this, 'commandAliases') || ! is_array($this->commandAliases)) {
            return [];
        }

        // Filtered rather than cast: the property belongs to the consuming
        // command, so its contents are not this trait's to assume. A stray null
        // would reach Symfony's setAliases() as a type error at boot.
        return array_values(array_filter(
            $this->commandAliases,
            static fn (mixed $alias): bool => is_string($alias) && $alias !== '',
        ));
    }

    private function writeName(string $property, mixed $value): void
    {
        // The properties are private on Symfony's base Command.
        new ReflectionProperty(SymfonyCommand::class, $property)->setValue($this, $value);
    }
}
