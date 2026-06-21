<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Interactive;

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Mockery;
use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Support\Live;
use Simtabi\Laranail\Console\Tools\Widgets\AnimatedBar;
use Simtabi\Laranail\Console\Tools\Widgets\Badge;
use Simtabi\Laranail\Console\Tools\Widgets\ButtonGroup;
use Simtabi\Laranail\Console\Tools\Widgets\Pill;
use Symfony\Component\Console\Output\BufferedOutput;

final class InteractiveTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
        // Prompt::fake() installs a static Mockery Terminal mock + forces interactive
        // mode; reset both so nothing leaks into later tests (this is a plain
        // PHPUnit TestCase, so Mockery isn't closed for us).
        Prompt::interactive(false);
        Mockery::close();
        parent::tearDown();
    }

    public function test_live_non_tty_writes_once_without_cursor_sequences(): void
    {
        Capabilities::fake(colors: false, unicode: true, interactive: false);
        $out = new BufferedOutput;

        Live::make($out)->refresh(static fn (int $i): string => "frame {$i}", 5, 0);

        $text = $out->fetch();
        self::assertSame("frame 4\n", $text);                 // only final frame
        self::assertStringNotContainsString("\033[", $text);  // no cursor control
    }

    public function test_animated_bar_determinate_and_indeterminate(): void
    {
        Capabilities::fake(colors: false, unicode: true, interactive: false);

        $bar = AnimatedBar::make()->width(20)->fraction(0.5)->render();
        self::assertStringContainsString('50%', $bar);
        self::assertLessThanOrEqual(20, DisplayWidth::of($bar));

        $moving = AnimatedBar::make()->width(20)->indeterminate(3)->render();
        self::assertStringContainsString('█', $moving);
        self::assertStringNotContainsString('%', $moving);
    }

    public function test_badge_and_pill_render(): void
    {
        Capabilities::fake(colors: false, unicode: true, interactive: false);

        self::assertSame(' OK ', Badge::success('OK')->render());
        self::assertSame('▐ OK ▌', Pill::make('OK')->render());
    }

    public function test_button_group_returns_scripted_choice(): void
    {
        Capabilities::fake(interactive: true);
        // A prior test that ran an Artisan command leaves Laravel\Prompts' console
        // fallback enabled, and fallbackWhen() is sticky (only OR-sets true), so reset
        // the static directly — otherwise select() uses the framework fallback instead
        // of the faked terminal.
        (new \ReflectionProperty(Prompt::class, 'shouldFallback'))->setValue(null, false);
        Prompt::fake([Key::DOWN, Key::ENTER]); // move to second option, select

        $choice = ButtonGroup::make(['save' => 'Save', 'discard' => 'Discard'])->prompt('Action');

        self::assertSame('discard', $choice);
    }
}
