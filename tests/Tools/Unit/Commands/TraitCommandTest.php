<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Commands;

use Illuminate\Console\Command as BaseCommand;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\InteractsWithConsoleServices;
use Simtabi\Laranail\Console\Tools\Commands\Services\CommandServiceManager;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * A command that does NOT extend our base — it extends Illuminate's command and
 * pulls in full console support via the trait alone.
 */
final class TraitOnlyCommand extends BaseCommand
{
    use InteractsWithConsoleServices;

    protected $signature = 'laranail-test:trait-only';

    protected $description = 'Trait-only console support test command';

    public function handle(): int
    {
        $this->services->metadata()->add('handled', true);
        $this->services->display()->info('hello from the trait');

        return self::SUCCESS;
    }
}

final class TraitCommandTest extends TestCase
{
    private function makeCommand(): TraitOnlyCommand
    {
        $command = new TraitOnlyCommand;
        $command->setLaravel($this->app);

        return $command;
    }

    public function test_trait_boots_the_service_manager_lazily(): void
    {
        $services = $this->makeCommand()->getServices();

        self::assertInstanceOf(CommandServiceManager::class, $services);
        self::assertSame('laranail-test:trait-only', $services->getCommandName());
    }

    public function test_trait_runs_the_full_lifecycle_without_extending_the_base(): void
    {
        $command = $this->makeCommand();
        $output = new BufferedOutput;

        $exit = $command->run(new ArrayInput([]), $output);

        self::assertSame(TraitOnlyCommand::SUCCESS, $exit);
        self::assertTrue($command->getServices()->metadata()->get('handled'));
        self::assertStringContainsString('hello from the trait', $output->fetch());
    }

    public function test_configure_services_is_fluent_via_trait(): void
    {
        $command = $this->makeCommand();

        self::assertSame($command, $command->configureServices(['native_events' => false]));
    }
}
