<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Tests\Validators;

use Simtabi\Laranail\Console\Prompter\Tests\TestCase;
use Simtabi\Laranail\Console\Prompter\Validators as V;
use stdClass;

final class AllValidatorsTest extends TestCase
{
    /**
     * @return array<string, array{0:V\AbstractValidator, 1:mixed, 2:mixed}>
     */
    public static function validators(): array
    {
        return [
            'alpha' => [new V\AlphaValidator('e'), 'abc', '123'],
            'alphanumeric' => [new V\AlphanumericValidator('e'), 'abc1', 'a b'],
            'array' => [new V\ArrayValidator('e'), [], 'x'],
            'boolean' => [new V\BooleanFieldValidator('e'), true, 'maybe'],
            'checkbox' => [new V\CheckboxFieldValidator('e'), true, 'x'],
            'color' => [new V\ColorValidator('e'), '#ffffff', 'reddish'],
            'date' => [new V\DateFieldValidator(null, 'e'), '2026-01-02', 'tomorrow'],
            'email' => [new V\EmailFieldValidator('e'), 'a@b.com', 'x'],
            'json' => [new V\JsonFieldValidator('e'), '{"a":1}', '{'],
            'name' => [new V\NameFieldValidator('e'), 'John Doe', '123'],
            'null_or_empty' => [new V\NullOrEmptyValidator('e'), '', 'x'],
            'number' => [new V\NumberFieldValidator('e'), '12', 'x'],
            'object' => [new V\ObjectValidator('e'), new stdClass, 'x'],
            'password' => [new V\PasswordFieldValidator('e'), 'abcd1234', 'short'],
            'path' => [new V\PathFieldValidator('e'), '/usr/local', '../etc'],
            'phone' => [new V\PhoneNumberValidator('e'), '+12345678901', 'abc'],
            'radio' => [new V\RadioFieldValidator(['a', 'b'], 'e'), 'a', 'c'],
            'select' => [new V\SelectFieldValidator(['a', 'b'], 'e'), 'a', 'c'],
            'string' => [new V\StringFieldValidator(0, 5, 'e'), 'abc', 'toolong'],
            'textarea' => [new V\TextAreaFieldValidator('e'), 'x', 123],
            'text' => [new V\TextFieldValidator('e'), 'x', 123],
            'time' => [new V\TimeFieldValidator(null, 'e'), '14:30', 'noon'],
            'username' => [new V\UsernameValidator('e'), 'john_doe', 'ab'],
            'uuid' => [new V\UUIDFieldValidator('e'), 'f47ac10b-58cc-4372-a567-0e02b2c3d479', 'x'],
            'uuid_or_int_or_slug' => [new V\UuidOrIntegerOrSlugValidator('uuid', 'e'), 'my-slug', '!!'],
        ];
    }

    /**
     * @dataProvider validators
     */
    public function test_validator_accepts_valid_and_rejects_invalid(V\AbstractValidator $validator, mixed $valid, mixed $invalid): void
    {
        self::assertNull($validator->validate($valid), 'should accept the valid value');
        self::assertSame('e', $validator->validate($invalid), 'should reject the invalid value');
    }

    /**
     * Every validator is total: non-string input returns an error, never throws.
     *
     * @dataProvider validators
     */
    public function test_validator_is_total_on_non_string_input(V\AbstractValidator $validator, mixed $valid, mixed $invalid): void
    {
        foreach ([[], null, 1.5, new stdClass] as $weird) {
            $result = $validator->validate($weird);
            self::assertTrue($result === null || is_string($result));
        }
    }
}
