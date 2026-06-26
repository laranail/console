<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Tests\Validators;

use Simtabi\Laranail\Console\Prompter\Tests\TestCase;
use Simtabi\Laranail\Console\Prompter\Validators\EmailFieldValidator;

/**
 * The v2.0 fluent message API: constructors take domain args only; the message,
 * replacements and locale are set fluently and resolved lazily at validate-time.
 */
final class FluentValidatorConfigTest extends TestCase
{
    public function test_error_message_override_wins(): void
    {
        self::assertSame('Custom', new EmailFieldValidator()->errorMessage('Custom')->validate('nope'));
    }

    public function test_default_uses_the_translated_message(): void
    {
        self::assertSame(__('console::validators.email'), new EmailFieldValidator()->validate('nope'));
    }

    public function test_setters_are_chainable_and_order_independent(): void
    {
        $v = new EmailFieldValidator()->locale(null)->replace([])->errorMessage('X');

        self::assertInstanceOf(EmailFieldValidator::class, $v);
        self::assertSame('X', $v->validate('nope'));
    }

    public function test_message_is_resolved_lazily_not_frozen_at_construction(): void
    {
        $v = new EmailFieldValidator;
        self::assertSame(__('console::validators.email'), $v->validate('nope')); // default at validate-time

        $v->errorMessage('Late');                                               // change config after construction
        self::assertSame('Late', $v->validate('nope'));                         // reflected => lazy
    }

    public function test_locale_and_replace_feed_the_translated_default(): void
    {
        // a throwaway locale with a placeholder message, registered at runtime
        app('translator')->addLines(['validators.email' => 'invalid :what'], 'xx', 'console');

        $msg = new EmailFieldValidator()->locale('xx')->replace(['what' => 'address'])->validate('nope');

        // proves locale() resolves in 'xx' AND replace() substitutes the placeholder
        self::assertSame('invalid address', $msg);
    }

    public function test_error_message_override_ignores_locale_and_replace(): void
    {
        app('translator')->addLines(['validators.email' => 'invalid :what'], 'xx', 'console');

        $msg = new EmailFieldValidator()
            ->locale('xx')
            ->replace(['what' => 'address'])
            ->errorMessage('Hard override')
            ->validate('nope');

        self::assertSame('Hard override', $msg); // override wins; no translation/substitution applied
    }
}
