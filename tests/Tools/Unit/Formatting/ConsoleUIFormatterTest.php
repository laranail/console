<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Formatting;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

final class ConsoleUIFormatterTest extends TestCase
{
    public function test_static_message_helpers_return_strings_containing_text(): void
    {
        self::assertStringContainsString('done', ConsoleUIFormatter::success('done'));
        self::assertStringContainsString('oops', ConsoleUIFormatter::error('oops'));
        self::assertStringContainsString('careful', ConsoleUIFormatter::warning('careful'));
        self::assertStringContainsString('note', ConsoleUIFormatter::info('note'));
    }

    public function test_fluent_builder_renders_message(): void
    {
        $out = ConsoleUIFormatter::create()->addMessage('hello')->render();

        self::assertStringContainsString('hello', $out);
    }

    public function test_colorize_applies_a_background_colour(): void
    {
        $previous = getenv('FORCE_COLOR');
        putenv('FORCE_COLOR=1');

        try {
            $out = ConsoleUIFormatter::create()->colorize('x', ConsoleUIFormatter::WHITE, false, ConsoleUIFormatter::BG_RED);

            // BG_RED must resolve to the red *background* SGR, not the foreground.
            self::assertStringContainsString("\033[41m", $out);
        } finally {
            $previous === false ? putenv('FORCE_COLOR') : putenv("FORCE_COLOR={$previous}");
        }
    }
}
