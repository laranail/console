<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Feature;

use Simtabi\Laranail\Console\Tools\Support\ConfigValidator;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;

final class ConfigValidationTest extends TestCase
{
    public function test_shipped_config_is_valid(): void
    {
        self::assertSame([], ConfigValidator::validate());
    }

    public function test_catches_a_bad_palette_colour(): void
    {
        config()->set('console.theme.palette', ['primary' => 'not-a-colour']);

        $errors = ConfigValidator::validate();
        self::assertNotEmpty($errors);
        self::assertStringContainsString('theme.palette.primary', $errors[0]);
    }

    public function test_catches_an_unknown_preset(): void
    {
        config()->set('console.theme.preset', 'bogus');

        self::assertNotEmpty(array_filter(ConfigValidator::validate(), fn (string $e): bool => str_contains($e, 'theme.preset')));
    }

    public function test_catches_a_bad_enum(): void
    {
        config()->set('console.output.symbols', 'sparkly');
        config()->set('console.emoji.mode', 'nope');

        $errors = ConfigValidator::validate();
        self::assertNotEmpty(array_filter($errors, fn (string $e): bool => str_contains($e, 'output.symbols')));
        self::assertNotEmpty(array_filter($errors, fn (string $e): bool => str_contains($e, 'emoji.mode')));
    }

    public function test_ignores_unknown_extra_keys(): void
    {
        config()->set('console.totally.unknown.key', 'whatever');

        self::assertSame([], ConfigValidator::validate());
    }

    public function test_check_command_passes_on_valid_config(): void
    {
        $this->artisan('laranail::console.check')->assertExitCode(0);
    }

    public function test_check_command_fails_on_invalid_config(): void
    {
        config()->set('console.theme.preset', 'bogus');

        $this->artisan('laranail::console.check')->assertExitCode(1);
    }
}
