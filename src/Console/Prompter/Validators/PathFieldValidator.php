<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

use Illuminate\Support\Facades\File;

/**
 * Class PathFieldValidator
 *
 * Validates path fields.
 */
class PathFieldValidator extends AbstractValidator
{

    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'path', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        return File::exists($value) && is_string($value) ? null : $this->errorMessage;
    }
}
