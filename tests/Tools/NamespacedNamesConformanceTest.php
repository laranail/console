<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests;

use Throwable;
use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\SupportsNamespacedNames;

/**
 * Conformance for the `laranail::<slug>.<command>` naming trait.
 *
 * This package holds the canonical copy — twenty-nine packages in the family import it. Three others
 * carry their own, for reasons that hold: `laranail/package-tools` must keep its `require` free of
 * any `laranail/*` entry, `laranail/db-tools` documents an independence invariant, and
 * `laranail/enumerator` targets PHP ^8.3 while this package targets ^8.4.1.
 *
 * Copies are tolerable. Copies that quietly stop agreeing are not: one read an undeclared
 * `$commandAliases` and fataled at boot for any command that did not declare the property, and the
 * fix for that introduced the opposite fatal. Every package carrying a copy asserts the same
 * behaviour, so a divergence fails somewhere instead of shipping.
 *
 * The assertions are the shared contract, not the file: each package writes them in its own house
 * style. Here that is a namespaced PHPUnit class — a top-level helper function makes the file
 * unmappable to Infection's coverage log, which fails mutation testing rather than any assertion.
 */
final class NamespacedNamesConformanceTest extends TestCase
{
    public function test_it_accepts_a_name_symfony_would_reject(): void
    {
        // ^[^:]++(:[^:]++)*$ rejects the empty segment in `::`, which is the entire reason the trait
        // exists. Dispatch still works: Symfony matches an exact name before splitting on `:`.
        $this->assertSame(
            'laranail::atlas.doctor',
            $this->command()->setName('laranail::atlas.doctor')->getName(),
        );
    }

    public function test_it_accepts_namespaced_aliases(): void
    {
        $this->assertSame(
            ['laranail::atlas.dr'],
            $this->command()->setAliases(['laranail::atlas.dr'])->getAliases(),
        );
    }

    public function test_it_takes_aliases_from_any_iterable(): void
    {
        $this->assertSame(
            ['laranail::atlas.x'],
            $this->command()->setAliases(new ArrayIterator(['laranail::atlas.x']))->getAliases(),
        );
    }

    public function test_it_returns_itself_so_the_calls_chain(): void
    {
        $command = $this->command();

        $this->assertSame($command, $command->setName('laranail::atlas.a'));
        $this->assertSame($command, $command->setAliases([]));
    }

    public function test_it_does_not_fatal_without_a_declared_alias_list(): void
    {
        // The bug this file exists for. A copy that reads $commandAliases without declaring it
        // throws on every command that does not — at boot, for the whole application.
        try {
            $this->assertSame('laranail::atlas.b', $this->command()->setName('laranail::atlas.b')->getName());
        } catch (Throwable $throwable) {
            $this->fail('setName() threw without a declared alias list: ' . $throwable->getMessage());
        }
    }

    public function test_it_still_reports_an_empty_name_as_empty(): void
    {
        // The trait bypasses validateName() deliberately, but an empty name is not a namespaced
        // name — it is Symfony's own "no name" state, and getName() should report it as such.
        $this->assertSame('', $this->command()->setName('')->getName());
    }

    private function command(): SymfonyCommand
    {
        return new class extends SymfonyCommand
        {
            use SupportsNamespacedNames;
        };
    }
}
