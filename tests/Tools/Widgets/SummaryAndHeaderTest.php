<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Widgets\Header;
use Simtabi\Laranail\Console\Tools\Widgets\Summary;

final class SummaryAndHeaderTest extends TestCase
{
    public function test_summary_renders_statistics_and_status(): void
    {
        $out = Summary::make([
            'total' => 3,
            'success' => 2,
            'failed' => 1,
            'totalTime' => 1234.0,
            'fastest' => ['class' => 'Fast', 'time' => 10.0],
            'slowest' => ['class' => 'Slow', 'time' => 900.0],
            'errors' => [['class' => 'Boom', 'type' => 'App\\X\\RuntimeProblem', 'message' => 'it broke']],
        ])->render();

        self::assertStringContainsString('EXECUTION SUMMARY', $out);
        self::assertStringContainsString('Success Rate', $out);
        self::assertStringContainsString('FAILED', $out);
        self::assertStringContainsString('it broke', $out);
        // class_basename is applied to error types, then rendered as an (upper-cased) badge.
        self::assertStringContainsString('RUNTIMEPROBLEM', $out);
    }

    public function test_summary_all_completed_when_no_failures(): void
    {
        $out = Summary::make(['total' => 2, 'success' => 2, 'failed' => 0, 'totalTime' => 5.0])->render();

        self::assertStringContainsString('ALL COMPLETED', $out);
    }

    public function test_header_renders_title_and_optional_count(): void
    {
        self::assertStringContainsString('Modules', Header::make('Modules')->render());

        $withCount = Header::make('Modules')->count(12, 'items')->render();
        self::assertStringContainsString('Modules', $withCount);
        self::assertStringContainsString('12 items', $withCount);
    }
}
