<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

/**
 * Validates color fields (RGB, RGBA, HEX).
 */
class ColorValidator extends AbstractValidator
{
    public function __construct(?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'color', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        if (! is_string($value)) {
            return $this->errorMessage;
        }

        $patterns = [
            '/^rgb\((\d{1,3}), (\d{1,3}), (\d{1,3})\)$/',
            '/^rgba\((\d{1,3}), (\d{1,3}), (\d{1,3}), (0|0?\.\d+|1(\.0)?)\)$/',
            '/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                return null;
            }
        }

        return $this->errorMessage;
    }
}
