<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class UsernameValidator
 *
 * Validates username fields.
 */
final class UsernameValidator extends RegexValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct('/^\w{3,20}$/', $errorMessage, 'username', $replace, $locale);
    }
}
