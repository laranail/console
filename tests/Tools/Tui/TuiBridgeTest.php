<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Tui;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\ConsoleManager;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\PanelBlock;
use Simtabi\Laranail\Console\Tui\RenderableWidget;
use Symfony\Component\Tui\Render\RenderContext;
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

    public function test_render_clips_lines_to_the_context_width(): void
    {
        // A line wider than the context must be clipped so symfony/tui's Renderer
        // does not throw RenderException.
        $widget = RenderableWidget::of(str_repeat('x', 100) . "\n" . str_repeat('y', 100));

        $lines = $widget->render(new RenderContext(20, 5));

        foreach ($lines as $line) {
            self::assertLessThanOrEqual(20, DisplayWidth::of($line));
        }
        // toLines() stays pure (full width).
        self::assertSame(100, DisplayWidth::of($widget->toLines()[0]));
    }

    public function test_console_tui_returns_a_tui_app_and_accepts_our_widgets(): void
    {
        $tui = (new ConsoleManager)->tui();
        self::assertInstanceOf(Tui::class, $tui);

        // Mounting our widget must not throw (add() returns the Tui).
        self::assertInstanceOf(Tui::class, $tui->add(RenderableWidget::of(Box::make(['x']))));
    }
}
