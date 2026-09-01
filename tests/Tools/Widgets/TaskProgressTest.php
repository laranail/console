<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Widgets\TaskProgress\TaskProgress;
use Symfony\Component\Console\Output\BufferedOutput;

final class TaskProgressTest extends TestCase
{
    public function test_non_tty_emits_a_line_per_state_change(): void
    {
        $out = new BufferedOutput;
        $tp = TaskProgress::make($out);

        $task = $tp->task('Compile', 2);
        $task->start();
        $tp->draw();
        $task->advance(2)->succeed();
        $tp->draw();

        $lines = array_values(array_filter(explode("\n", $out->fetch())));

        // One line when it started (running), one when it succeeded.
        self::assertGreaterThanOrEqual(2, count($lines));
        self::assertStringContainsString('Compile', $lines[0]);
    }

    public function test_exit_code_and_summary_reflect_failure(): void
    {
        $out = new BufferedOutput;
        $tp = TaskProgress::make($out);
        $tp->task('a')->succeed();
        $tp->task('b')->fail('boom');

        self::assertSame(1, $tp->finish());
        self::assertStringContainsString('1/2 tasks succeeded', $out->fetch());
    }

    public function test_all_success_exits_zero(): void
    {
        $out = new BufferedOutput;
        $tp = TaskProgress::make($out);
        $tp->task('a')->succeed();

        self::assertSame(0, $tp->finish());
    }
}
