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

    public function test_short_class_name_helper(): void
    {
        self::assertSame('Baz', ConsoleUIFormatter::getShortClassName('Foo\\Bar\\Baz'));
    }
}
