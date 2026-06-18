<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Formatting;

use Override;
use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleProgressBar;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * A ConsoleOutput whose writes are captured into an in-memory buffer
 * instead of being printed to stdout. The class under test types its
 * output property as ?ConsoleOutput, so a subclass is required.
 */
final class CapturingConsoleOutput extends ConsoleOutput
{
    public string $buffer = '';

    #[Override]
    protected function doWrite(string $message, bool $newline): void
    {
        $this->buffer .= $message . ($newline ? "\n" : '');
    }
}

final class ConsoleProgressBarTest extends TestCase
{
    /**
     * Replace the real ConsoleOutput (which writes to stdout) with a capturing
     * one so progress output is buffered rather than printed during tests.
     */
    private function withBufferedOutput(ConsoleProgressBar $bar, CapturingConsoleOutput $buffer): void
    {
        $bar->consoleOutput = $buffer;
    }

    public function test_constructor_starts_with_no_active_progress_bar(): void
    {
        $bar = new ConsoleProgressBar;

        self::assertNull($bar->progressBar);
        self::assertNull($bar->getTaskLabel());
        self::assertInstanceOf(ConsoleUIFormatter::class, $bar->getFormatter());
    }

    public function test_set_task_label_is_fluent(): void
    {
        $bar = new ConsoleProgressBar;

        self::assertSame($bar, $bar->setTaskLabel('Indexing'));
        self::assertSame('Indexing', $bar->getTaskLabel());
    }

    public function test_set_task_label_with_formatting_records_label(): void
    {
        $bar = new ConsoleProgressBar;

        $bar->setTaskLabelWithFormatting('Build', ConsoleUIFormatter::GREEN, [ConsoleUIFormatter::BOLD]);

        self::assertSame('Build', $bar->getTaskLabel());
    }

    public function test_start_creates_progress_bar_sized_to_count_of_array(): void
    {
        $bar = new ConsoleProgressBar;
        $this->withBufferedOutput($bar, new CapturingConsoleOutput);

        $bar->startProgressBar('Items', ['a', 'b', 'c']);

        self::assertInstanceOf(ProgressBar::class, $bar->progressBar);
        self::assertSame(3, $bar->progressBar->getMaxSteps());
        self::assertSame('Items', $bar->getTaskLabel());
    }

    public function test_start_with_integer_uses_it_as_total(): void
    {
        $bar = new ConsoleProgressBar;
        $this->withBufferedOutput($bar, new CapturingConsoleOutput);

        $bar->startProgressBar('Count', 7);

        self::assertSame(7, $bar->progressBar->getMaxSteps());
    }

    public function test_advance_moves_the_progress_bar(): void
    {
        $bar = new ConsoleProgressBar;
        $this->withBufferedOutput($bar, new CapturingConsoleOutput);
        $bar->startProgressBar('Items', 10);

        $bar->advanceProgressBar(2, 0);

        self::assertSame(2, $bar->progressBar->getProgress());
    }

    public function test_advance_clamps_non_positive_step_to_one(): void
    {
        $bar = new ConsoleProgressBar;
        $this->withBufferedOutput($bar, new CapturingConsoleOutput);
        $bar->startProgressBar('Items', 10);

        $bar->advanceProgressBar(0, 0);

        self::assertSame(1, $bar->progressBar->getProgress());
    }

    public function test_finish_resets_state_and_writes_output(): void
    {
        $bar = new ConsoleProgressBar;
        $buffer = new CapturingConsoleOutput;
        $this->withBufferedOutput($bar, $buffer);
        $bar->startProgressBar('Indexing', 4);
        $bar->advanceProgressBar(4, 0);

        $bar->finishProgressBar('done');

        // After finishing, internal state is reset back to inactive.
        self::assertNull($bar->progressBar);
        self::assertNull($bar->getTaskLabel());
        self::assertNotSame('', $buffer->buffer);
    }

    public function test_finish_with_badge_resets_state(): void
    {
        $bar = new ConsoleProgressBar;
        $this->withBufferedOutput($bar, new CapturingConsoleOutput);
        $bar->startProgressBar('Indexing', 2);

        $bar->finishProgressBarWithBadge('complete');

        self::assertNull($bar->progressBar);
        self::assertNull($bar->getTaskLabel());
    }

    public function test_display_status_is_fluent_and_writes_message(): void
    {
        $bar = new ConsoleProgressBar;
        $buffer = new CapturingConsoleOutput;
        $this->withBufferedOutput($bar, $buffer);

        self::assertSame($bar, $bar->displayStatus('all good', 'success'));
        self::assertStringContainsString('all good', $buffer->buffer);
    }

    public function test_display_badge_is_fluent_and_writes_message(): void
    {
        $bar = new ConsoleProgressBar;
        $buffer = new CapturingConsoleOutput;
        $this->withBufferedOutput($bar, $buffer);

        self::assertSame($bar, $bar->displayBadge('READY'));
        self::assertStringContainsString('READY', $buffer->buffer);
    }

    public function test_iterate_walks_data_through_the_progress_bar(): void
    {
        $bar = new ConsoleProgressBar;
        $this->withBufferedOutput($bar, new CapturingConsoleOutput);
        $bar->startProgressBar('Items', 3);

        $seen = [];
        foreach ($bar->iterate(['x', 'y', 'z']) as $item) {
            $seen[] = $item;
        }

        self::assertSame(['x', 'y', 'z'], $seen);
    }
}
