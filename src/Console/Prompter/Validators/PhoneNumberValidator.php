<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class PhoneNumberValidator
 *
 * Validates phone number fields.
 */
class PhoneNumberValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'phone', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\+?\d{10,15}$/', $value) === 1 ? null : $this->errorMessage;
    }
}
