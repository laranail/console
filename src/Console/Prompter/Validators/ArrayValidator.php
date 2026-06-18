<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class ArrayValidator
 *
 * Validates array fields.
 */
class ArrayValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'array', $replace, $locale);
    }
    public function validate(mixed $value): ?string
    {
        return is_array($value) ? null : $this->errorMessage;
    }
}
