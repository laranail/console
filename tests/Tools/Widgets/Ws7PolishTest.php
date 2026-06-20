<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Symbols;
use Simtabi\Laranail\Console\Tools\Widgets\Menu\Menu;
use Simtabi\Laranail\Console\Tools\Widgets\Spinner;
use Simtabi\Laranail\Console\Tools\Widgets\Tree;

final class Ws7PolishTest extends TestCase
{
    public function test_spinner_elapsed_appends_a_duration(): void
    {
        $line = Spinner::make('Working')->elapsed()->start()->frameLine();

        self::assertStringContainsString('Working', $line);
        self::assertMatchesRegularExpression('/\d+(\.\d+)?s/', $line, 'elapsed time present');

        // Without elapsed the line ends at the message.
        self::assertStringEndsWith('Working', Spinner::make('Working')->start()->frameLine());
    }

    public function test_tree_from_array_builds_branches_and_leaves(): void
    {
        $out = Tree::fromArray('app', [
            'Console' => ['Commands', 'Widgets'],
            'Providers',
            'config' => ['console.php'],
        ])->render();

        foreach (['app', 'Console', 'Commands', 'Widgets', 'Providers', 'console.php'] as $needle) {
            self::assertStringContainsString($needle, $out);
        }
    }

    public function test_tree_renders_the_status_glyph(): void
    {
        $glyph = Symbols::for(Capabilities::detect())->get('success');

        $out = Tree::make('root')->child('node', fn (Tree $t): Tree => $t->status('success'))->render();

        self::assertStringContainsString($glyph . ' node', $out);
    }

    public function test_menu_radio_groups_toggle_independently(): void
    {
        $menu = Menu::make()
            ->addRadio('a', 'A', group: 'env')
            ->addRadio('b', 'B', group: 'env')
            ->addRadio('x', 'X', group: 'tier')
            ->addRadio('y', 'Y', group: 'tier');

        $items = $menu->items();
        $toggle = new ReflectionMethod($menu, 'toggle');

        $toggle->invoke($menu, $items[0]); // env → A
        $toggle->invoke($menu, $items[2]); // tier → X
        $toggle->invoke($menu, $items[1]); // env → B (unchecks A; X stays)

        self::assertFalse($items[0]->checked, 'A unchecked by sibling');
        self::assertTrue($items[1]->checked, 'B checked');
        self::assertTrue($items[2]->checked, 'X in other group unaffected');
        self::assertFalse($items[3]->checked, 'Y never selected');
    }
}
