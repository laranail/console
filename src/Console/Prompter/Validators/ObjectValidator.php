<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class ObjectValidator
 *
 * Validates object fields.
 */
class ObjectValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'object', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return is_object($value) ? null : $this->errorMessage;
    }
}
