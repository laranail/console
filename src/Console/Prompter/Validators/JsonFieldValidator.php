<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Validates JSON fields.
 */
final class JsonFieldValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'json', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return $this->errorMessage;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE ? null : $this->errorMessage;
    }
}
