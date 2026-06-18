<?php declare(strict_types=1);

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
        return preg_match('/^\+?[0-9]{10,15}$/', $value) ? null : $this->errorMessage;
    }
}
