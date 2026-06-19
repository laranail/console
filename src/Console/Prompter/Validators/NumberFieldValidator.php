<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class NumberFieldValidator
 *
 * Validates number fields.
 */
class NumberFieldValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'number', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return is_numeric($value) ? null : $this->errorMessage;
    }
}
