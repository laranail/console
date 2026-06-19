<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Tui;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\ConsoleManager;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\PanelBlock;
use Simtabi\Laranail\Console\Tui\RenderableWidget;
use Symfony\Component\Tui\Tui;

final class TuiBridgeTest extends TestCase
{
    public function test_wraps_a_stringable_widget_into_lines(): void
    {
        $box = Box::make(['hello']);

        self::assertSame(explode("\n", $box->render()), RenderableWidget::of($box)->toLines());
    }

    public function test_wraps_a_renderable_widget_via_render_lines(): void
    {
        $block = PanelBlock::make("a\nb")->border();

        self::assertSame($block->renderLines(), RenderableWidget::of($block)->toLines());
    }

    public function test_wraps_a_raw_string(): void
    {
        self::assertSame(['one', 'two'], RenderableWidget::of("one\ntwo")->toLines());
    }

    public function test_console_tui_returns_a_tui_app_and_accepts_our_widgets(): void
    {
        $tui = (new ConsoleManager)->tui();
        self::assertInstanceOf(Tui::class, $tui);

        // Mounting our widget must not throw (add() returns the Tui).
        self::assertInstanceOf(Tui::class, $tui->add(RenderableWidget::of(Box::make(['x']))));
    }
}
