<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Validates RFC 4122 UUID fields (any version 1–5).
 */
class UUIDFieldValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'uuid', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        $pattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/';

        return is_string($value) && preg_match($pattern, $value) === 1 ? null : $this->errorMessage;
    }
}
