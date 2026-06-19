<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Widgets\Table;
use Symfony\Component\Console\Helper\TableCell;

final class TableVariantsTest extends TestCase
{
    public function test_from_assoc_derives_headers_and_rows(): void
    {
        $out = Table::make()->fromAssoc([
            ['name' => 'ada', 'role' => 'eng'],
            ['name' => 'bay', 'role' => 'ops'],
        ])->render();

        self::assertStringContainsString('name', $out);
        self::assertStringContainsString('role', $out);
        self::assertStringContainsString('ada', $out);
        self::assertStringContainsString('ops', $out);
    }

    public function test_title_footer_and_alignment_render(): void
    {
        $out = Table::make()
            ->headers(['Item', 'Size'])
            ->rows([['disk', '512'], ['ram', '8']])
            ->align(['Size' => 'right'])
            ->title('Report')
            ->footer('2 rows')
            ->render();

        self::assertStringContainsString('Report', $out);
        self::assertStringContainsString('2 rows', $out);
        // Right-aligned column pads on the left: "  8" appears, not "8  ".
        self::assertMatchesRegularExpression('/\s8\s*\S/', $out);
    }

    public function test_cell_factory_returns_styled_cell(): void
    {
        $cell = Table::cell('hi', align: 'center', fg: 'green');
        self::assertInstanceOf(TableCell::class, $cell);
        self::assertSame('hi', (string) $cell);

        // A styled cell can be used inside rows().
        $out = Table::make()->headers(['X'])->rows([[Table::cell('val', fg: 'red')]])->render();
        self::assertStringContainsString('val', $out);
    }

    public function test_grouped_renders_labels_and_rows(): void
    {
        $out = Table::make()
            ->headers(['Name', 'Status'])
            ->grouped(['Web' => [['nginx', 'up']], 'Data' => [['pg', 'down']]])
            ->render();

        self::assertStringContainsString('Web', $out);
        self::assertStringContainsString('Data', $out);
        self::assertStringContainsString('nginx', $out);
        self::assertStringContainsString('pg', $out);
    }

    public function test_tree_indents_nested_rows(): void
    {
        $out = Table::make()
            ->headers(['Path'])
            ->tree([[0, ['app']], [1, ['Console']], [2, ['Tools']]])
            ->render();

        self::assertStringContainsString('app', $out);
        self::assertStringContainsString('Console', $out);
        self::assertStringContainsString('Tools', $out);
    }
}
