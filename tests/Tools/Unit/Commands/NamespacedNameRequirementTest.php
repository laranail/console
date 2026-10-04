<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Commands;

use Illuminate\Contracts\Console\Kernel;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Illuminate\Console\Command as IlluminateCommand;
use Simtabi\Laranail\Console\Tools\Commands\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\SupportsNamespacedNames;

/**
 * Pins what `docs/tools/commands.md` says a command needs for a `laranail::<slug>.<command>` name:
 * extending the base `Command` is not enough, the `SupportsNamespacedNames` trait is required too.
 *
 * The base does not compose the trait, so a `::` signature on a plain subclass reaches Symfony's
 * `validateName()` and throws while the command is being constructed -- which, for a command
 * registered by a provider, is when the application boots.
 */
final class NamespacedNameRequirementTest extends TestCase
{
    public function test_the_base_alone_rejects_a_namespaced_name_at_construction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command name "laranail::console-test.base-only" is invalid.');

        new class extends Command
        {
            protected $signature = 'laranail::console-test.base-only';

            public function handle(): int
            {
                return self::SUCCESS;
            }
        };
    }

    public function test_the_base_with_the_trait_accepts_and_dispatches_a_namespaced_name(): void
    {
        $command = new class extends Command
        {
            use SupportsNamespacedNames;

            protected $signature = 'laranail::console-test.with-trait';

            public function handle(): int
            {
                $this->line('dispatched');

                return self::SUCCESS;
            }
        };

        self::assertSame('laranail::console-test.with-trait', $command->getName());

        $this->app->make(Kernel::class)->registerCommand($command);

        $this->artisan('laranail::console-test.with-trait')
            ->expectsOutput('dispatched')
            ->assertExitCode(0);
    }

    public function test_the_trait_works_on_a_base_from_outside_this_package(): void
    {
        $command = new class extends IlluminateCommand
        {
            use SupportsNamespacedNames;

            protected $signature = 'laranail::console-test.plain-base';

            public function handle(): int
            {
                return self::SUCCESS;
            }
        };

        $this->app->make(Kernel::class)->registerCommand($command);

        $this->artisan('laranail::console-test.plain-base')->assertExitCode(0);
    }
}
