<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Commands;

use LogicException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArrayInput;
use Simtabi\Laranail\Console\Tools\Support\Live;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Symfony\Component\Console\Output\StreamOutput;
use Illuminate\Console\Command as IlluminateCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Simtabi\Laranail\Console\Tools\Widgets\TaskProgress\TaskProgress;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\SupportsNamespacedNames;

/**
 * A terminal stand-in that reports as a `ConsoleOutputInterface`, as Symfony's `ConsoleOutput`
 * does, and hands out real sections over one in-memory stream so a test can read what was drawn.
 */
final class SectionedStreamOutput extends StreamOutput implements ConsoleOutputInterface
{
    public int $sectionsOpened = 0;

    /** @var list<ConsoleSectionOutput> */
    private array $sections = [];

    public function __construct(bool $decorated)
    {
        $stream = fopen('php://memory', 'w+b');

        if ($stream === false) {
            throw new LogicException('Cannot open php://memory.');
        }

        parent::__construct($stream, decorated: $decorated);
    }

    public function getErrorOutput(): OutputInterface
    {
        return $this;
    }

    public function setErrorOutput(OutputInterface $error): void
    {
        throw new LogicException('The error stream is not used by these tests.');
    }

    public function section(): ConsoleSectionOutput
    {
        $this->sectionsOpened++;

        return new ConsoleSectionOutput($this->getStream(), $this->sections, $this->getVerbosity(), $this->isDecorated(), $this->getFormatter());
    }

    public function contents(): string
    {
        $stream = $this->getStream();
        rewind($stream);

        return (string) stream_get_contents($stream);
    }
}

/**
 * Animates three frames through the command's own output, which `run()` has wrapped.
 */
final class LiveFramesCommand extends IlluminateCommand
{
    use SupportsNamespacedNames;

    protected $signature = 'laranail::console-test.live-frames';

    protected $description = 'Live render routing test command';

    public function handle(): int
    {
        Live::make($this->output)->refresh(static fn (int $i): string => "frame {$i}", 3, 0);

        return self::SUCCESS;
    }
}

/**
 * Drives a TaskProgress through the command's own output, which `run()` has wrapped.
 */
final class TaskProgressFramesCommand extends IlluminateCommand
{
    use SupportsNamespacedNames;

    protected $signature = 'laranail::console-test.task-progress';

    protected $description = 'TaskProgress render routing test command';

    public function handle(): int
    {
        $progress = TaskProgress::make($this->output);
        $task = $progress->task('Compile', 2);
        $task->start();
        $progress->draw();
        $task->advance(2)->succeed();
        $progress->draw();

        return self::SUCCESS;
    }
}

/**
 * Live rendering through the real `Illuminate\Console\Command::run()` path.
 *
 * `run()` wraps the output it is given in an `OutputStyle` before `handle()` sees it, and
 * `OutputStyle` is not a `ConsoleOutputInterface`. These tests hand a command a decorated console
 * output and run it the way `php artisan` does, so a bare `instanceof` check on the wrapper fails
 * them; a bare Symfony output would skip the wrapper and pass on the defect.
 */
final class LiveRenderRoutingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = $this->app->make(Kernel::class);
        $kernel->registerCommand(new LiveFramesCommand);
        $kernel->registerCommand(new TaskProgressFramesCommand);
    }

    protected function tearDown(): void
    {
        Capabilities::clearFake();
        parent::tearDown();
    }

    public function test_live_redraws_in_place_inside_a_command_on_a_decorated_terminal(): void
    {
        Capabilities::fake(colors: false, unicode: false, interactive: true);
        $output = new SectionedStreamOutput(decorated: true);

        self::assertSame(0, $this->runCommand('laranail::console-test.live-frames', $output));

        self::assertSame(1, $output->sectionsOpened);
        self::assertStringContainsString('frame 0', $output->contents());
        self::assertStringContainsString('frame 2', $output->contents());
        self::assertStringContainsString("\033[", $output->contents());
    }

    public function test_live_falls_back_to_one_static_frame_on_an_undecorated_terminal(): void
    {
        Capabilities::fake(colors: false, unicode: false, interactive: true);
        $output = new SectionedStreamOutput(decorated: false);

        self::assertSame(0, $this->runCommand('laranail::console-test.live-frames', $output));

        self::assertSame(0, $output->sectionsOpened);
        self::assertSame('frame 2' . PHP_EOL, $output->contents());
    }

    public function test_live_falls_back_to_one_static_frame_without_a_tty(): void
    {
        Capabilities::fake(colors: false, unicode: false, interactive: false);
        $output = new SectionedStreamOutput(decorated: true);

        self::assertSame(0, $this->runCommand('laranail::console-test.live-frames', $output));

        self::assertSame(0, $output->sectionsOpened);
        self::assertSame('frame 2' . PHP_EOL, $output->contents());
    }

    public function test_task_progress_redraws_in_place_inside_a_command_on_a_decorated_terminal(): void
    {
        Capabilities::fake(colors: false, unicode: false, interactive: true);
        $output = new SectionedStreamOutput(decorated: true);

        self::assertSame(0, $this->runCommand('laranail::console-test.task-progress', $output));

        self::assertSame(1, $output->sectionsOpened);
        self::assertStringContainsString("\033[", $output->contents());
    }

    public function test_task_progress_emits_one_line_per_state_change_on_an_undecorated_terminal(): void
    {
        Capabilities::fake(colors: false, unicode: false, interactive: true);
        $output = new SectionedStreamOutput(decorated: false);

        self::assertSame(0, $this->runCommand('laranail::console-test.task-progress', $output));

        self::assertSame(0, $output->sectionsOpened);
        self::assertStringNotContainsString("\033[", $output->contents());
        self::assertCount(2, array_filter(explode(PHP_EOL, $output->contents())));
    }

    public function test_task_progress_emits_one_line_per_state_change_without_a_tty(): void
    {
        Capabilities::fake(colors: false, unicode: false, interactive: false);
        $output = new SectionedStreamOutput(decorated: true);

        self::assertSame(0, $this->runCommand('laranail::console-test.task-progress', $output));

        self::assertSame(0, $output->sectionsOpened);
        self::assertCount(2, array_filter(explode(PHP_EOL, $output->contents())));
    }

    private function runCommand(string $name, SectionedStreamOutput $output): int
    {
        $command = Artisan::all()[$name];
        self::assertInstanceOf(IlluminateCommand::class, $command);

        return $command->run(new ArrayInput(['command' => $name]), $output);
    }
}
