<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class UsernameValidator
 *
 * Validates username fields.
 */
class UsernameValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'username', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $value) ? null : $this->errorMessage;
    }
}
