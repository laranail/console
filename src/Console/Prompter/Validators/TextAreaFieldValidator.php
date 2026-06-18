<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class TextAreaFieldValidator
 *
 * Validates textarea fields.
 */
class TextAreaFieldValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'textarea', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return is_string($value) ? null : $this->errorMessage;
    }
}
