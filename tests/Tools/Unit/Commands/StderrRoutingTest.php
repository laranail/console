<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Commands;

use LogicException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArrayInput;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Symfony\Component\Console\Output\StreamOutput;
use Illuminate\Console\Command as IlluminateCommand;
use Simtabi\Laranail\Console\Tools\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\SupportsNamespacedNames;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\InteractsWithConsoleWriter;

/**
 * A terminal stand-in: stdout and stderr are separate in-memory streams, as they are for a real
 * `ConsoleOutput`, so a test can tell which one a line went to.
 */
final class SplitStreamOutput extends StreamOutput implements ConsoleOutputInterface
{
    private OutputInterface $stderr;

    public function __construct()
    {
        parent::__construct($this->memory(), decorated: false);
        $this->stderr = new StreamOutput($this->memory(), decorated: false);
    }

    public function getErrorOutput(): OutputInterface
    {
        return $this->stderr;
    }

    public function setErrorOutput(OutputInterface $error): void
    {
        $this->stderr = $error;
    }

    public function section(): ConsoleSectionOutput
    {
        throw new LogicException('Sections are not used by these tests.');
    }

    public function stdout(): string
    {
        return $this->read($this);
    }

    public function stderr(): string
    {
        return $this->stderr instanceof StreamOutput ? $this->read($this->stderr) : '';
    }

    /** @return resource */
    private function memory()
    {
        $stream = fopen('php://memory', 'w+b');

        if ($stream === false) {
            throw new LogicException('Cannot open php://memory.');
        }

        return $stream;
    }

    private function read(StreamOutput $output): string
    {
        $stream = $output->getStream();
        rewind($stream);

        return (string) stream_get_contents($stream);
    }
}

/**
 * A command on the package's base with one bare deprecated alias.
 */
final class StderrAliasCommand extends Command
{
    use SupportsNamespacedNames;

    protected $signature = 'laranail::console-test.stderr-alias';

    protected $description = 'Deprecated-alias stream test command';

    /** @var list<string> */
    protected array $deprecatedCommandAliases = ['console-test:stderr-old'];

    public function handle(): int
    {
        $this->line('handled');

        return self::SUCCESS;
    }
}

/**
 * Emits a ConsoleWriter error line from inside a command, through the command's own output.
 */
final class StderrWriterCommand extends IlluminateCommand
{
    use InteractsWithConsoleWriter;
    use SupportsNamespacedNames;

    protected $signature = 'laranail::console-test.stderr-writer';

    protected $description = 'ConsoleWriter stderr routing test command';

    public function handle(): int
    {
        $this->consoleWriter()->line('result');
        $this->consoleWriter()->error('boom');
        $this->consoleWriter()->toStderr()->line('diagnostic');

        return self::SUCCESS;
    }
}

/**
 * Stream routing through the real `Illuminate\Console\Command::run()` path.
 *
 * `run()` wraps the output it is given in an `OutputStyle` before `initialize()` and `handle()`
 * see it, so these tests hand a command an output with a separate error stream and run it the way
 * `php artisan` does. A bare Symfony output would skip the wrapper and pass on the defect.
 */
final class StderrRoutingTest extends TestCase
{
    private const string WARNING = 'Deprecated: [console-test:stderr-old] is a deprecated alias and will be removed in the next minor after 0.1. Use [laranail::console-test.stderr-alias] instead.';

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = $this->app->make(Kernel::class);
        $kernel->registerCommand(new StderrAliasCommand);
        $kernel->registerCommand(new StderrWriterCommand);
    }

    protected function tearDown(): void
    {
        Capabilities::clearFake();
        parent::tearDown();
    }

    public function test_a_deprecated_alias_warns_on_stderr_and_leaves_stdout_unchanged(): void
    {
        $output = new SplitStreamOutput;

        self::assertSame(0, $this->runCommand('console-test:stderr-old', $output));

        self::assertSame('handled' . PHP_EOL, $output->stdout());
        self::assertSame(self::WARNING . PHP_EOL, $output->stderr());
    }

    public function test_stdout_of_the_alias_matches_stdout_of_the_canonical_name(): void
    {
        $byAlias = new SplitStreamOutput;
        $byName = new SplitStreamOutput;

        $this->runCommand('console-test:stderr-old', $byAlias);
        $this->runCommand('laranail::console-test.stderr-alias', $byName);

        self::assertSame($byName->stdout(), $byAlias->stdout());
        self::assertSame('', $byName->stderr());
    }

    public function test_console_writer_errors_and_to_stderr_reach_stderr_from_a_command(): void
    {
        Capabilities::fake(unicode: false);
        $output = new SplitStreamOutput;

        self::assertSame(0, $this->runCommand('laranail::console-test.stderr-writer', $output));

        self::assertSame('result' . PHP_EOL, $output->stdout());
        self::assertStringContainsString('boom', $output->stderr());
        self::assertStringContainsString('diagnostic', $output->stderr());
    }

    private function runCommand(string $invokedAs, SplitStreamOutput $output): int
    {
        $command = Artisan::all()[$invokedAs];
        self::assertInstanceOf(IlluminateCommand::class, $command);

        return $command->run(new ArrayInput(['command' => $invokedAs]), $output);
    }
}
