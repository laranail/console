<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class TextFieldValidator
 *
 * Validates text fields.
 */
class TextFieldValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'text', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return is_string($value) && strlen($value) <= 255 ? null : $this->errorMessage;
    }
}
