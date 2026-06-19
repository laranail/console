<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class SelectFieldValidator
 *
 * Validates select fields.
 */
final class SelectFieldValidator extends AbstractValidator
{
    public function __construct(protected array $options, ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'select', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return in_array($value, $this->options, true) ? null : $this->errorMessage;
    }
}
