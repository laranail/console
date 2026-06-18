<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class EmailFieldValidator
 *
 * Validates email fields.
 */
class EmailFieldValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'email', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? null : $this->errorMessage;
    }
}
