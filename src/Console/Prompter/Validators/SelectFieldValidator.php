<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class SelectFieldValidator
 *
 * Validates select fields.
 */
class SelectFieldValidator extends AbstractValidator
{
    protected array $options;

    public function __construct(array $options, ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'select', $replace, $locale);
        $this->options = $options;
    }

    public function validate(mixed $value): ?string
    {
        return in_array($value, $this->options, true) ? null : $this->errorMessage;
    }
}
