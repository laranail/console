<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Console\Kernel;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Illuminate\Console\Command as IlluminateCommand;
use Simtabi\Laranail\Console\Tools\Commands\Command;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\WarnsOnDeprecatedAlias;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\SupportsNamespacedNames;

/**
 * A command on the package's base, with a vendor-scoped alias and a bare deprecated one.
 */
final class DeprecatedAliasBaseCommand extends Command
{
    use SupportsNamespacedNames;

    protected $signature = 'laranail::console-test.renamed';

    protected $description = 'Deprecated-alias test command';

    /** @var list<string> */
    protected array $commandAliases = ['laranail::console-test.rn'];

    /** @var list<string> */
    protected array $deprecatedCommandAliases = ['console-test:old-name', 'laranail::console-test.renamed'];

    public function handle(): int
    {
        $this->line('handled');

        return self::SUCCESS;
    }
}

/**
 * The same, on Laravel's own base: the trait has to work without the package's command base.
 */
final class DeprecatedAliasTraitOnlyCommand extends IlluminateCommand
{
    use SupportsNamespacedNames;
    use WarnsOnDeprecatedAlias;

    protected $signature = 'laranail::console-test.trait-only';

    protected $description = 'Deprecated-alias trait-only test command';

    /** @var list<string> */
    protected array $deprecatedCommandAliases = ['console-test:trait-old'];

    public function handle(): int
    {
        $this->line('handled');

        return self::SUCCESS;
    }
}

final class DeprecatedAliasTest extends TestCase
{
    private const string WARNING = 'is a deprecated alias';

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = $this->app->make(Kernel::class);
        $kernel->registerCommand(new DeprecatedAliasBaseCommand);
        $kernel->registerCommand(new DeprecatedAliasTraitOnlyCommand);
    }

    public function test_the_deprecated_alias_is_registered_in_the_live_application(): void
    {
        $commands = Artisan::all();

        self::assertArrayHasKey('console-test:old-name', $commands);
        self::assertInstanceOf(DeprecatedAliasBaseCommand::class, $commands['console-test:old-name']);
        self::assertArrayHasKey('console-test:trait-old', $commands);
        self::assertInstanceOf(DeprecatedAliasTraitOnlyCommand::class, $commands['console-test:trait-old']);
    }

    public function test_a_bare_deprecated_alias_runs_and_warns_naming_the_canonical_command(): void
    {
        self::assertSame(0, Artisan::call('console-test:old-name'));

        $output = Artisan::output();

        self::assertStringContainsString('handled', $output);
        self::assertStringContainsString(
            'Deprecated: [console-test:old-name] is a deprecated alias and will be removed in the next minor after 0.1. Use [laranail::console-test.renamed] instead.',
            $output,
        );
    }

    public function test_the_warning_prints_before_the_command_runs(): void
    {
        Artisan::call('console-test:old-name');

        $output = Artisan::output();

        self::assertLessThan(strpos($output, 'handled'), strpos($output, self::WARNING));
    }

    public function test_the_canonical_name_does_not_warn(): void
    {
        self::assertSame(0, Artisan::call('laranail::console-test.renamed'));

        $output = Artisan::output();

        self::assertStringContainsString('handled', $output);
        self::assertStringNotContainsString(self::WARNING, $output);
    }

    public function test_a_vendor_scoped_alias_that_is_not_deprecated_does_not_warn(): void
    {
        self::assertSame(0, Artisan::call('laranail::console-test.rn'));

        $output = Artisan::output();

        self::assertStringContainsString('handled', $output);
        self::assertStringNotContainsString(self::WARNING, $output);
    }

    public function test_the_trait_works_without_the_package_base(): void
    {
        self::assertSame(0, Artisan::call('console-test:trait-old'));
        self::assertStringContainsString('Use [laranail::console-test.trait-only] instead.', Artisan::output());

        self::assertSame(0, Artisan::call('laranail::console-test.trait-only'));
        self::assertStringNotContainsString(self::WARNING, Artisan::output());
    }

    public function test_the_command_name_is_never_treated_as_deprecated(): void
    {
        self::assertSame(['console-test:old-name'], new DeprecatedAliasBaseCommand()->deprecatedCommandAliases());
        self::assertSame(
            ['laranail::console-test.rn', 'console-test:old-name'],
            new DeprecatedAliasBaseCommand()->getAliases(),
        );
    }

    public function test_a_command_without_the_property_has_no_deprecated_aliases(): void
    {
        $command = new class extends Command
        {
            protected $signature = 'laranail-test:no-deprecated';

            public function handle(): int
            {
                return self::SUCCESS;
            }
        };

        self::assertSame([], $command->deprecatedCommandAliases());
        self::assertSame([], $command->getAliases());
    }
}
