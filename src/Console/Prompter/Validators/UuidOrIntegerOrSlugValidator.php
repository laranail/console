<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

use Illuminate\Support\Str;

/**
 * Class UuidOrIntegerOrSlugValidator
 *
 * Validates that the input is either a UUID, an integer ID, or a slug.
 */
class UuidOrIntegerOrSlugValidator extends AbstractValidator
{
    public function __construct(protected string $uuidVersion = 'uuid', ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'uuid_or_integer_or_slug', $replace, $locale);
    }

    public function validate(mixed $value): ?string
    {
        if ($this->isUuid($value) || $this->isInteger($value) || $this->isSlug($value)) {
            return null;
        }

        return $this->errorMessage;
    }

    /**
     * Check if the value is a valid UUID.
     */
    private function isUuid(mixed $value): bool
    {
        return match ($this->uuidVersion) {
            'uuid1' => is_string($value) && Str::isUuid($value) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-1[0-9a-fA-F]{3}-[89ab][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $value),
            'uuid3' => is_string($value) && Str::isUuid($value) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-3[0-9a-fA-F]{3}-[89ab][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $value),
            'uuid4' => is_string($value) && Str::isUuid($value) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89ab][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $value),
            'uuid5' => is_string($value) && Str::isUuid($value) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-5[0-9a-fA-F]{3}-[89ab][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $value),
            default => is_string($value) && Str::isUuid($value),
        };
    }

    /**
     * Check if the value is a valid integer.
     */
    private function isInteger(mixed $value): bool
    {
        return is_int($value) || (is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false);
    }

    /**
     * Check if the value is a valid slug.
     */
    private function isSlug(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value);
    }
}
