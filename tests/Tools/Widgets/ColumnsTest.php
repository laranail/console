<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Widgets\Columns;

final class ColumnsTest extends TestCase
{
    public function test_lays_items_into_a_fixed_column_count_column_major(): void
    {
        // 10 items into 3 columns → ceil(10/3) = 4 rows, filled down-then-across.
        $out = Columns::make(array_map(strval(...), range(1, 10)))->columns(3)->render();
        $lines = explode("\n", $out);

        self::assertCount(4, $lines);
        // Column-major: row 0 = items 1, 5, 9 (col0 rows0..3, col1 starts at 5).
        self::assertStringContainsString('1', $lines[0]);
        self::assertStringContainsString('5', $lines[0]);
        self::assertStringContainsString('9', $lines[0]);
    }

    public function test_empty_is_empty_and_single_item_is_one_line(): void
    {
        self::assertSame('', Columns::make([])->render());
        self::assertSame('only', Columns::make(['only'])->render());
    }

    public function test_auto_fit_keeps_lines_within_terminal_width(): void
    {
        $out = Columns::make(array_map(static fn (int $i): string => "item-{$i}", range(1, 40)))->render();

        foreach (explode("\n", $out) as $line) {
            self::assertLessThanOrEqual(120, mb_strlen($line));
        }
        self::assertStringContainsString('item-1', $out);
        self::assertStringContainsString('item-40', $out);
    }
}
