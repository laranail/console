<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Widgets\Panel;
use Simtabi\Laranail\Console\Tools\Widgets\PanelBlock;

final class PanelTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
        parent::tearDown();
    }

    /**
     * @return list<int>
     */
    private function widths(string $rendered): array
    {
        return array_map(DisplayWidth::of(...), explode("\n", $rendered));
    }

    public function test_block_renders_bordered_equal_width(): void
    {
        $out = PanelBlock::make("hi\nthere")->border()->render();
        $widths = $this->widths($out);

        self::assertCount(1, array_unique($widths));
        self::assertSame(4, PanelBlock::make('1234')->totalWidth());
    }

    public function test_horizontal_panel_aligns_multibyte_columns(): void
    {
        $panel = Panel::make()->horizontal()->dividers()
            ->add(PanelBlock::make("left\nrow"))
            ->add(PanelBlock::make('日本')); // wide characters

        $rendered = $panel->render();

        self::assertCount(1, array_unique($this->widths($rendered)), 'columns must align despite wide chars');
        self::assertStringContainsString('日本', $rendered);
    }

    public function test_vertical_panel_with_border_and_dividers(): void
    {
        Capabilities::fake(unicode: true); // assert Unicode borders deterministically (CI runners vary)

        $rendered = Panel::make()->border()->dividers()
            ->add(PanelBlock::make('one'))
            ->add(PanelBlock::make('two'))
            ->render();

        $lines = explode("\n", $rendered);

        self::assertStringContainsString('one', $rendered);
        self::assertStringContainsString('two', $rendered);
        self::assertCount(1, array_unique($this->widths($rendered)));
        self::assertStringStartsWith('┌', $lines[0]);
    }

    public function test_panels_nest(): void
    {
        $inner = Panel::make()->add(PanelBlock::make('inner'));
        $outer = Panel::make()->border()->add($inner)->add(PanelBlock::make('outer'));

        $rendered = $outer->render();

        self::assertStringContainsString('inner', $rendered);
        self::assertStringContainsString('outer', $rendered);
        self::assertCount(1, array_unique($this->widths($rendered)));
    }
}
