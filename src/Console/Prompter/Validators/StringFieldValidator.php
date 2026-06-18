<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Validates string fields with character-length constraints.
 */
class StringFieldValidator extends AbstractValidator
{
    protected int $minLength;
    protected int $maxLength;

    public function __construct(int $minLength = 0, int $maxLength = 255, ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'string', $replace, $locale);

        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
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
