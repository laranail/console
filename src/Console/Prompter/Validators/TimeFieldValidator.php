<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

use DateTimeImmutable;

/**
 * Validates time fields against explicit time formats.
 */
final class TimeFieldValidator extends AbstractValidator
{
    /** @var list<string> */
    protected array $formats;

    /**
     * @param list<string>|null $formats
     */
    public function __construct(?array $formats = null, ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'time', $replace, $locale);

        $this->formats = $formats ?? ['H:i', 'H:i:s', 'g:i A', 'g:i a'];
    }

    public function validate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return $this->errorMessage;
        }

        foreach ($this->formats as $format) {
            $time = DateTimeImmutable::createFromFormat('!' . $format, $value);
            $errors = DateTimeImmutable::getLastErrors();

            if ($time !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return null;
            }
        }

        return $this->errorMessage;
    }
}
