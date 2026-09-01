<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Tests;

use Simtabi\Laranail\Console\Prompter\Enums\ContextType;
use Simtabi\Laranail\Console\Prompter\Exceptions\PrompterException;
use Simtabi\Laranail\Console\Prompter\Prompter;
use Simtabi\Laranail\Console\Prompter\Services\PromptService;

final class PrompterTest extends TestCase
{
    public function test_create_returns_isolated_instances(): void
    {
        self::assertNotSame(Prompter::create(), Prompter::create());
    }

    public function test_container_binding_is_not_a_singleton(): void
    {
        self::assertNotSame(app(Prompter::class), app(Prompter::class));
    }

    /**
     * Regression: prompt methods live in PromptService's closure map, so the
     * dispatcher must recognise them via has() (method_exists would be false).
     */
    public function test_prompt_methods_are_dispatchable(): void
    {
        $service = new PromptService;

        foreach (['text', 'password', 'select', 'confirm', 'form', 'spin', 'progress'] as $method) {
            self::assertTrue($service->has($method), "{$method} should be dispatchable");
        }

        self::assertFalse($service->has('definitelyNotAPrompt'));
    }

    public function test_context_methods_are_recognised(): void
    {
        foreach (['note', 'info', 'warning', 'error', 'alert', 'intro', 'outro'] as $method) {
            self::assertInstanceOf(ContextType::class, ContextType::tryFrom($method));
        }
    }

    public function test_unknown_method_throws_prompter_exception(): void
    {
        $this->expectException(PrompterException::class);

        Prompter::create()->totallyUnknownMethod();
    }
}
