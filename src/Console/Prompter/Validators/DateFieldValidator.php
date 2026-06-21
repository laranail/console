<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Validates date fields against explicit, unambiguous formats.
 */
final class DateFieldValidator extends DateTimeFormatValidator
{
    /**
     * @param list<string>|null $formats
     */
    public function __construct(?array $formats = null, ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'date', $replace, $locale);

        $this->formats = $formats ?? ['Y-m-d', 'Y/m/d', 'd-m-Y', 'd/m/Y', 'm/d/Y'];
    }
}
