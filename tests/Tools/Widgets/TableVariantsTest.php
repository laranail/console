<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Widgets\Table;

final class TableVariantsTest extends TestCase
{
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
