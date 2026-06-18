<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

use Simtabi\Laranail\Console\Prompter\Contracts\ValidatorInterface;

/**
 * Abstract Class AbstractValidator
 *
 * Abstract base class for validators.
 */
abstract class AbstractValidator implements ValidatorInterface
{
    protected string $errorMessage;

    /**
     * AbstractValidator constructor.
     *
     * @param string|null $errorMessage The error message to use if validation fails.
     * @param string $defaultMessageKey The default message key for translation.
     */
    public function __construct(?string $errorMessage = null, string $defaultMessageKey = '', array $replace = [], ?string $locale = null)
    {
        $this->errorMessage = $errorMessage ?? __('console::validators.' . $defaultMessageKey, $replace, $locale);
    }

    /**
     * Validate the given value.
     *
     * @param mixed $value The value to validate.
     * @return string|null The error message if validation fails, or null if validation passes.
     */
    abstract public function validate(mixed $value): ?string;
}
