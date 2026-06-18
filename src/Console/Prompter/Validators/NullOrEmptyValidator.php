<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class NullOrEmptyValidator
 *
 * Validates null or empty fields.
 */
class NullOrEmptyValidator extends AbstractValidator
{

    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'null_or_empty', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return is_null($value) || $value === '' ? null : $this->errorMessage;
    }
}
