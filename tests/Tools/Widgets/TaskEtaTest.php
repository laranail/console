<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Widgets\TaskProgress\Task;

final class TaskEtaTest extends TestCase
{
    public function test_eta_is_null_without_a_total_or_progress(): void
    {
        self::assertNull(new Task('no-total')->eta());

        $started = new Task('started', 10);
        $started->start();
        self::assertNull($started->eta(), 'no progress yet → no ETA');
    }

    public function test_eta_estimates_remaining_time_from_elapsed_progress(): void
    {
        $task = new Task('work', 4);
        // Pin the start two seconds in the past, then record one of four steps:
        // estimated total = 2s / 1 * 4 = 8s, so ETA ≈ 6s remaining.
        $task->startedAt = microtime(true) - 2.0;
        $task->advance();

        $eta = $task->eta();
        self::assertNotNull($eta);
        self::assertGreaterThan(3.0, $eta);
        self::assertLessThan(9.0, $eta);
    }

    public function test_eta_is_zero_once_finished(): void
    {
        $task = new Task('work', 4);
        $task->startedAt = microtime(true) - 1.0;
        $task->advance();
        $task->succeed();

        self::assertSame(0.0, $task->eta());
    }
}
