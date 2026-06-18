<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class UUIDFieldValidator
 *
 * Validates UUID fields.
 */
class UUIDFieldValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'uuid', $replace, $locale);
    }

    /**
     * Validate the given value.
     *
     * @param mixed $value The value to validate.
     * @return string|null The error message if validation fails, or null if validation passes.
     */
    public function validate(mixed $value): ?string
    {
        if (is_string($value) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[4][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $value)) {
            return null;
        }

        return $this->errorMessage;
    }
}
