<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Tests\Validators;

use Simtabi\Laranail\Console\Prompter\Tests\TestCase;
use Simtabi\Laranail\Console\Prompter\Validators\AlphaValidator;
use Simtabi\Laranail\Console\Prompter\Validators\ColorValidator;
use Simtabi\Laranail\Console\Prompter\Validators\DateFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\EmailFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\JsonFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\PathFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\SelectFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\UUIDFieldValidator;
use stdClass;

final class ValidatorSecurityTest extends TestCase
{
    /**
     * C2: validators must be total — non-string input returns an error, never a TypeError.
     */
    public function test_validators_do_not_throw_on_non_string_input(): void
    {
        $validators = [
            new AlphaValidator()->errorMessage('e'),
            new ColorValidator()->errorMessage('e'),
            new DateFieldValidator()->errorMessage('e'),
            new EmailFieldValidator()->errorMessage('e'),
            new JsonFieldValidator()->errorMessage('e'),
            new PathFieldValidator()->errorMessage('e'),
            new UUIDFieldValidator()->errorMessage('e'),
        ];

        foreach ($validators as $validator) {
            foreach ([123, [], null, new stdClass, 1.5, true] as $input) {
                self::assertSame('e', $validator->validate($input), $validator::class . ' on ' . gettype($input));
            }
        }
    }

    /**
     * C1: PathFieldValidator validates shape only and rejects traversal/null bytes,
     * never probing the filesystem.
     */
    public function test_path_validator_rejects_traversal_and_null_bytes(): void
    {
        $validator = new PathFieldValidator()->errorMessage('bad');

        self::assertNull($validator->validate('/usr/local/bin/app'));
        self::assertNull($validator->validate('relative/path.txt'));
        self::assertSame('bad', $validator->validate('../etc/passwd'));
        self::assertSame('bad', $validator->validate("/tmp/\0evil"));
        self::assertSame('bad', $validator->validate(''));
    }

    /**
     * C3: choice allow-lists use strict comparison (no type juggling).
     */
    public function test_select_validator_is_strict(): void
    {
        $validator = new SelectFieldValidator(['admin', 'user'])->errorMessage('bad');

        self::assertNull($validator->validate('admin'));
        self::assertSame('bad', $validator->validate(0));
        self::assertSame('bad', $validator->validate('0'));
        self::assertSame('bad', $validator->validate(true));
    }

    public function test_date_validator_rejects_relative_strings(): void
    {
        $validator = new DateFieldValidator()->errorMessage('bad');

        self::assertNull($validator->validate('2026-01-02'));
        self::assertSame('bad', $validator->validate('tomorrow'));
        self::assertSame('bad', $validator->validate('next friday'));
    }

    public function test_uuid_validator_accepts_any_version(): void
    {
        $validator = new UUIDFieldValidator()->errorMessage('bad');

        self::assertNull($validator->validate('a0eebc99-9c0b-11d1-80b4-00c04fd430c8')); // v1
        self::assertNull($validator->validate('f47ac10b-58cc-4372-a567-0e02b2c3d479')); // v4
        self::assertSame('bad', $validator->validate('not-a-uuid'));
    }
}
