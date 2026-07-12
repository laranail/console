<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Tests\Validators;

use Simtabi\Laranail\Console\Prompter\Tests\TestCase;
use Simtabi\Laranail\Console\Prompter\Validators\PasswordFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\StringFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\TextFieldValidator;

/**
 * Length validators count characters (mb_strlen), not bytes — matching the
 * "characters" message text, StringFieldValidator, and Laravel's string rules.
 */
final class LengthCountingTest extends TestCase
{
    public function test_text_counts_characters_not_bytes(): void
    {
        $v = new TextFieldValidator()->errorMessage('e');

        // 200 accented chars = 400 bytes; passes on chars (would fail on bytes <= 255)
        self::assertNull($v->validate(str_repeat('é', 200)));
        // exactly at the 255-char limit (510 bytes)
        self::assertNull($v->validate(str_repeat('é', 255)));
        // over the char limit -> rejected
        self::assertSame('e', $v->validate(str_repeat('é', 256)));
    }

    public function test_password_counts_characters_not_bytes(): void
    {
        $v = new PasswordFieldValidator()->errorMessage('e');

        // 3 emoji = 12 bytes but only 3 chars -> now correctly fails the 8-char minimum
        self::assertSame('e', $v->validate('🚀🚀🚀'));
        // 8 multibyte chars (16 bytes) -> passes
        self::assertNull($v->validate(str_repeat('é', 8)));
        // plain 8-char password still passes (ASCII unchanged)
        self::assertNull($v->validate('abcd1234'));
        self::assertSame('e', $v->validate('short'));
    }

    public function test_string_validator_remains_character_based(): void
    {
        $v = new StringFieldValidator(0, 5)->errorMessage('e');

        self::assertNull($v->validate('éàçio'));        // 5 chars (10 bytes)
        self::assertSame('e', $v->validate('éàçios'));   // 6 chars
    }
}
