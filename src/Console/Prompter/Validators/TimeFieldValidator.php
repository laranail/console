<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Validates time fields against explicit time formats.
 */
final class TimeFieldValidator extends DateTimeFormatValidator
{
    /**
     * @param list<string>|null $formats
     */
    public function __construct(?array $formats = null, ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'time', $replace, $locale);

        $this->formats = $formats ?? ['H:i', 'H:i:s', 'g:i A', 'g:i a'];
    }
}
