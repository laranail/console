<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class NameFieldValidator
 *
 * Validates name fields (full name / first and/or last name).
 */
class NameFieldValidator extends RegexValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct('/^[\p{L} \'-]+$/u', $errorMessage, 'name', $replace, $locale);
    }
}
