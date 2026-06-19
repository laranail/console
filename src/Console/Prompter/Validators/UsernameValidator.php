<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class UsernameValidator
 *
 * Validates username fields.
 */
class UsernameValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'username', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\w{3,20}$/', $value) === 1 ? null : $this->errorMessage;
    }
}
