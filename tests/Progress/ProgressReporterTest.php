<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Progress\Tests;

use Laravel\Prompts\Prompt;
use Simtabi\Laranail\Console\Progress\ProgressReporter;
use Simtabi\Laranail\Console\Progress\ProgressReporterFactory;
use Simtabi\Laranail\Console\Progress\PromptsProgressReporter;
use Simtabi\Laranail\Console\Progress\TuiProgressReporter;

final class ProgressReporterTest extends TestCase
{
    public function test_default_reporter_is_the_prompts_renderer(): void
    {
        config()->set('console.tui.progress', false);

        self::assertInstanceOf(PromptsProgressReporter::class, ProgressReporterFactory::make());
        self::assertInstanceOf(PromptsProgressReporter::class, app(ProgressReporter::class));
    }

    public function test_tui_renderer_is_selected_when_opted_in_and_installed(): void
    {
        // symfony/tui is a dev dependency, so it is installed here.
        config()->set('console.tui.progress', true);

        self::assertTrue(ProgressReporterFactory::tuiEnabled());
        self::assertInstanceOf(TuiProgressReporter::class, ProgressReporterFactory::make());
    }

    public function test_tui_opt_in_falls_back_to_prompts_when_package_absent(): void
    {
        // tuiEnabled() requires both the opt-in AND the symfony/tui class; when the
        // class is missing the factory must still return a working reporter.
        config()->set('console.tui.progress', false);

        self::assertFalse(ProgressReporterFactory::tuiEnabled());
        self::assertInstanceOf(PromptsProgressReporter::class, ProgressReporterFactory::make());
    }

    public function test_prompts_reporter_runs_the_callback_for_every_step(): void
    {
        Prompt::fake();

        $seen = [];

        (new PromptsProgressReporter)->run(
            'Working',
            ['a', 'b', 'c'],
            function (string $step) use (&$seen): string {
                $seen[] = $step;

                return strtoupper($step);
            },
        );

        self::assertSame(['a', 'b', 'c'], $seen);
    }
}
