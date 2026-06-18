<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Validators;

use Illuminate\Support\Str;

/**
 * Class UuidOrIntegerOrSlugValidator
 *
 * Validates that the input is either a UUID, an integer ID, or a slug.
 */
class UuidOrIntegerOrSlugValidator extends AbstractValidator
{
    protected string $uuidVersion;

    public function __construct(string $uuidVersion = 'uuid', ?string $errorMessage = null, array $replace = [], ?string $locale = null)
    {
        parent::__construct($errorMessage, 'uuid_or_integer_or_slug', $replace, $locale);
        $this->uuidVersion = $uuidVersion;
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
     *
     * @param mixed $value
     * @return bool
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
     *
     * @param mixed $value
     * @return bool
     */
    private function isInteger(mixed $value): bool
    {
        return is_numeric($value) && (int)$value == $value;
    }

    /**
     * Check if the value is a valid slug.
     *
     * @param mixed $value
     * @return bool
     */
    private function isSlug(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value);
    }
}
