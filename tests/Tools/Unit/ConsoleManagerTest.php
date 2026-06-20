<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit;

use Simtabi\Laranail\Console\ConsoleManager;
use Simtabi\Laranail\Console\Facades\Console;
use Simtabi\Laranail\Console\Prompter\Prompter;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Simtabi\Laranail\Console\Tools\Widgets\Box;
use Simtabi\Laranail\Console\Tools\Widgets\Spinner;
use Simtabi\Laranail\Console\Tools\Widgets\TaskProgress\TaskProgress;
use Simtabi\Laranail\Console\Tools\Widgets\Tree;

final class ConsoleManagerTest extends TestCase
{
    public function test_manager_is_bound_as_a_singleton(): void
    {
        self::assertInstanceOf(ConsoleManager::class, app(ConsoleManager::class));
        self::assertSame(app(ConsoleManager::class), app(ConsoleManager::class));
    }

    public function test_facade_exposes_both_sub_domains(): void
    {
        self::assertInstanceOf(ConsoleUIFormatter::class, Console::ui());
        self::assertInstanceOf(Prompter::class, Console::prompter());
        self::assertInstanceOf(Spinner::class, Console::spinner('x'));
        self::assertInstanceOf(Box::class, Console::box(['x']));
        self::assertInstanceOf(Tree::class, Console::tree('root'));
        self::assertInstanceOf(TaskProgress::class, Console::tasks());
    }

    public function test_config_is_published_and_merged(): void
    {
        self::assertSame('detailed', config('console.progress.format'));
        self::assertContains('https', config('console.links.allowed_schemes'));
    }

    public function test_translations_resolve_under_console_namespace(): void
    {
        self::assertSame('Invalid input. Please try again.', __('console::console.invalid_input'));
        self::assertSame('The input must be a valid email address.', __('console::validators.email'));
    }
}
