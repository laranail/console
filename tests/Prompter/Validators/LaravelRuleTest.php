<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Tests\Validators;

use Simtabi\Laranail\Console\Prompter\Tests\TestCase;
use Simtabi\Laranail\Console\Prompter\Validators\LaravelRule;

final class LaravelRuleTest extends TestCase
{
    public function test_passes_returns_null(): void
    {
        self::assertNull((new LaravelRule(['required', 'email']))->validate('a@b.com'));
    }

    public function test_failure_returns_first_laravel_message(): void
    {
        $result = (new LaravelRule(['required', 'email']))->validate('not-an-email');

        self::assertIsString($result);
        self::assertNotSame('', $result);
    }

    public function test_explicit_message_overrides(): void
    {
        $result = (new LaravelRule(['email'], [], 'Bad address'))->validate('nope');

        self::assertSame('Bad address', $result);
    }
}
