<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Validates string fields with character-length constraints.
 */
final class StringFieldValidator extends AbstractValidator
{
    public function __construct(protected int $minLength = 0, protected int $maxLength = 255, ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'string', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        if (! is_string($value)) {
            return $this->errorMessage;
        }

        $length = mb_strlen($value);

        return $length >= $this->minLength && $length <= $this->maxLength ? null : $this->errorMessage;
    }
}
