<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

use DateTimeImmutable;

/**
 * Validates date fields against explicit, unambiguous formats.
 */
final class DateFieldValidator extends AbstractValidator
{
    /** @var list<string> */
    protected array $formats;

    /**
     * @param list<string>|null $formats
     */
    public function __construct(?array $formats = null, ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'date', $replace, $locale);

        $this->formats = $formats ?? ['Y-m-d', 'Y/m/d', 'd-m-Y', 'd/m/Y', 'm/d/Y'];
    }

    public function validate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return $this->errorMessage;
        }

        foreach ($this->formats as $format) {
            $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
            $errors = DateTimeImmutable::getLastErrors();

            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return null;
            }
        }

        return $this->errorMessage;
    }
}
