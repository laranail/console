<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class PhoneNumberValidator
 *
 * Validates phone number fields.
 */
class PhoneNumberValidator extends RegexValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct('/^\+?\d{10,15}$/', $errorMessage, 'phone', $replace, $locale);
    }
}
