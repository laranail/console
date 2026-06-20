<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Tests;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Prompter\Exceptions\PrompterException;
use Simtabi\Laranail\Console\Prompter\Prompter;

final class PrompterParityTest extends TestCase
{
    /**
     * Every public laravel/prompts helper should be dispatchable through the
     * Prompter (pure check — no prompt is actually rendered).
     */
    public function test_prompter_recognises_every_prompts_helper(): void
    {
        $prompts = Prompter::create()->getPrompts();

        $helpers = [
            'text', 'textarea', 'password', 'confirm', 'select', 'multiselect',
            'suggest', 'search', 'multisearch', 'pause', 'spin', 'progress',
            'form', 'table', 'note', 'info', 'warning', 'error', 'alert', 'intro',
            'outro', 'clear', 'number', 'autocomplete', 'datatable', 'grid',
            'notify', 'title', 'stream', 'task',
        ];

        foreach ($helpers as $helper) {
            self::assertTrue(
                $prompts->has($helper),
                "Prompter should expose the laravel/prompts helper: {$helper}",
            );
        }
    }

    public function test_unknown_method_throws(): void
    {
        $this->expectException(PrompterException::class);

        Prompter::create()->thisIsNotARealPrompt();
    }
}
