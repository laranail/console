<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Class RadioFieldValidator
 *
 * Validates radio fields.
 */
class RadioFieldValidator extends AbstractValidator
{
    protected array $options;

    public function __construct(array $options, ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'radio', $replace, $locale);
        $this->options = $options;
    }

    public function validate(mixed $value): ?string
    {
        return in_array($value, $this->options) ? null : $this->errorMessage;
    }
}
