<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class AlphaValidator
 *
 * Validates fields that should contain only alphabetic characters.
 */
class AlphaValidator extends RegexValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct('/^[a-zA-Z]+$/', $errorMessage, 'alpha', $replace, $locale);
    }
}
