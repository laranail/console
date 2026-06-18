<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class AlphaValidator
 *
 * Validates fields that should contain only alphabetic characters.
 */
class AlphaValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'alpha', $replace, $locale);
    }
    public function validate(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^[a-zA-Z]+$/', $value) === 1 ? null : $this->errorMessage;
    }
}
