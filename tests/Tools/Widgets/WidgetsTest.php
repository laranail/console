<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Widgets\Banner;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\Callout;
use Simtabi\Laranail\Console\Tools\Widgets\Gauge;
use Simtabi\Laranail\Console\Tools\Widgets\ProgressBar;
use Simtabi\Laranail\Console\Tools\Widgets\Rule;
use Simtabi\Laranail\Console\Tools\Widgets\Sparkline;
use Simtabi\Laranail\Console\Tools\Widgets\Spinner;
use Simtabi\Laranail\Console\Tools\Widgets\StepFlow;
use Simtabi\Laranail\Console\Tools\Widgets\Table;
use Simtabi\Laranail\Console\Tools\Widgets\TaskProgress\TaskProgress;
use Simtabi\Laranail\Console\Tools\Widgets\Tree;
use Symfony\Component\Console\Output\BufferedOutput;

final class WidgetsTest extends TestCase
{
    /**
     * Every line of a rendered block occupies the same visible width.
     */
    private function assertAligned(string $rendered): void
    {
        $lines = explode("\n", $rendered);
        $widths = array_map(DisplayWidth::of(...), $lines);

        self::assertCount(1, array_unique($widths), 'Lines are not equal width: ' . implode(',', $widths));
    }

    public function test_box_lines_are_aligned_even_with_wide_chars(): void
    {
        $this->assertAligned(Box::make(['short', 'a wider line 日本'])->title('T')->render());
    }

    public function test_box_carries_title_and_footer(): void
    {
        $rendered = Box::make(['body'])->title('Config')->footer('ok')->render();

        self::assertStringContainsString('Config', $rendered);
        self::assertStringContainsString('ok', $rendered);
    }

    public function test_callout_is_aligned(): void
    {
        $this->assertAligned(Callout::warning("line one\nline two")->title('Heads up')->render());
    }

    public function test_tree_renders_nodes(): void
    {
        $rendered = Tree::make('root')
            ->child('a', fn (Tree $t): Tree => $t->child('a1'))
            ->child('b')
            ->render();

        self::assertStringContainsString('root', $rendered);
        self::assertStringContainsString('a1', $rendered);
        self::assertSame(4, substr_count($rendered, "\n") + 1);
    }

    public function test_gauge_shows_percentage(): void
    {
        $rendered = Gauge::make(72, 100)->label('Disk')->showValue()->render();

        self::assertStringContainsString('72%', $rendered);
        self::assertStringContainsString('(72/100)', $rendered);
    }

    public function test_sparkline(): void
    {
        self::assertNotSame('', Sparkline::make([1, 2, 3])->render());
        self::assertSame('', Sparkline::make([])->render());
    }

    public function test_rule_width_and_title(): void
    {
        self::assertSame(20, DisplayWidth::of(Rule::make()->width(20)->render()));
        self::assertStringContainsString('SETUP', Rule::make('SETUP')->width(40)->render());
    }

    public function test_rule_never_exceeds_width_even_with_a_long_title(): void
    {
        // A titled rule must always honour the requested width, degrading to a
        // plain rule when the title can't fit (rather than blanking/overflowing).
        foreach ([3, 4, 5, 6, 10] as $width) {
            $rendered = Rule::make('A Very Long Section Title')->width($width)->render();
            self::assertSame($width, DisplayWidth::of($rendered), "width $width left/overflow");

            $centered = Rule::make('A Very Long Section Title')->width($width)->center()->render();
            self::assertSame($width, DisplayWidth::of($centered), "width $width center/overflow");
        }
    }

    public function test_table_contains_headers_and_rows(): void
    {
        $rendered = Table::make()->headers(['Name'])->rows([['app']])->render();

        self::assertStringContainsString('Name', $rendered);
        self::assertStringContainsString('app', $rendered);
    }

    public function test_step_flow(): void
    {
        $rendered = StepFlow::make(['Detect', 'Plan', 'Apply'])->current(1)->render();

        self::assertStringContainsString('Detect', $rendered);
        self::assertStringContainsString('Apply', $rendered);
    }

    public function test_banner_contains_title(): void
    {
        self::assertStringContainsString('APP', Banner::make('APP')->width(20)->render());
    }

    public function test_progress_bar_renders(): void
    {
        $out = new BufferedOutput;
        (new ProgressBar($out, 5))->start()->advance(2)->finish();

        self::assertNotSame('', $out->fetch());
    }

    public function test_spinner_run_returns_callback_result(): void
    {
        self::assertSame('value', Spinner::make('work')->run(fn (): string => 'value'));
    }

    public function test_task_progress_exit_code_reflects_failures(): void
    {
        $out = new BufferedOutput;

        $ok = TaskProgress::make($out);
        $ok->task('a')->succeed();
        self::assertSame(0, $ok->exitCode());

        $bad = TaskProgress::make($out);
        $bad->task('a')->succeed();
        $bad->task('b')->fail('boom');
        self::assertSame(1, $bad->finish());
    }
}
