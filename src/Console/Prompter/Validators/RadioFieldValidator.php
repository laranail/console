<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class RadioFieldValidator
 *
 * Validates radio fields.
 */
final class RadioFieldValidator extends AbstractValidator
{
    public function __construct(protected array $options, ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'radio', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return in_array($value, $this->options, true) ? null : $this->errorMessage;
    }
}
