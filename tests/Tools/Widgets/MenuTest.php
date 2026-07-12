<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Widgets\Menu\CheckboxItem;
use Simtabi\Laranail\Console\Tools\Widgets\Menu\Menu;
use Simtabi\Laranail\Console\Tools\Widgets\Menu\MenuItem;
use Simtabi\Laranail\Console\Tools\Widgets\Menu\StaticItem;
use Simtabi\Laranail\Console\Tools\Widgets\Menu\SubMenuItem;

final class MenuTest extends TestCase
{
    public function test_builder_models_options_list_and_assoc(): void
    {
        $list = Menu::make('Pick', ['Apple', 'Banana'])->items();
        self::assertInstanceOf(MenuItem::class, $list[0]);
        self::assertSame('Apple', $list[0]->value, 'list options use the label as value');
        self::assertSame('Apple', $list[0]->label());

        $assoc = Menu::make()->addOptions(['a' => 'Apple', 'b' => 'Banana'])->items();
        self::assertSame('a', $assoc[0]->value);
        self::assertSame('Apple', $assoc[0]->label());
    }

    public function test_builder_supports_all_item_types(): void
    {
        $items = Menu::make('Main')
            ->addOption('o', 'Option')
            ->addStaticItem('— section —')
            ->addCheckbox('c', 'Check', true)
            ->addRadio('r', 'Radio')
            ->addQuestion('Your name?')
            ->addSubMenu('More', fn (Menu $m): Menu => $m->addOption('x', 'Nested'))
            ->items();

        self::assertCount(6, $items);
        self::assertInstanceOf(StaticItem::class, $items[1]);
        self::assertFalse($items[1]->selectable());
        self::assertInstanceOf(SubMenuItem::class, $items[5]);
        self::assertSame('Nested', $items[5]->submenu->items()[0]->label());
    }

    public function test_render_frame_marks_cursor_and_checkboxes(): void
    {
        $menu = Menu::make('Pizza')
            ->addOption('m', 'Mozzarella')
            ->addCheckbox('x', 'Extra cheese', true);

        $frame = $menu->renderFrame(0);
        self::assertStringContainsString('Mozzarella', $frame);
        self::assertStringContainsString('> ', $frame);
        self::assertStringContainsString('[x] Extra cheese', $frame);
        self::assertStringContainsString('Pizza', $frame);
        self::assertStringContainsString('Exit', $frame);
    }

    public function test_checkbox_item_toggles(): void
    {
        $item = new CheckboxItem('x', 'X');
        self::assertFalse($item->checked);
        $item->toggle();
        self::assertTrue($item->checked);
    }

    public function test_open_returns_null_when_nothing_is_selectable(): void
    {
        // Only static items → no prompt is invoked, open() short-circuits to null.
        self::assertNull(Menu::make('Empty')->addStaticItem('just a label')->addLineBreak()->open());
    }
}
