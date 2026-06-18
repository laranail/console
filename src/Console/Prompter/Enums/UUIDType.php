<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Enums;

/**
 * Enum UUIDType
 *
 * Represents various UUID types.
 */
enum UUIDType: string
{
    case UUID1 = 'uuid1';
    case UUID3 = 'uuid3';
    case UUID4 = 'uuid4';
    case UUID5 = 'uuid5';
    case DEFAULT = 'uuid';

    /**
     * Get all UUID types as array keys with custom labels.
     */
    public static function keysWithLabels(): array
    {
        return [
            self::UUID1->value => 'UUID1',
            self::UUID3->value => 'UUID3',
            self::UUID4->value => 'UUID4',
            self::UUID5->value => 'UUID5',
            self::DEFAULT->value => 'Default UUID',
        ];
    }

    /**
     * Get all enum values as array keys.
     */
    public static function keys(): array
    {
        $keys = [];
        foreach (self::cases() as $case) {
            $keys[$case->value] = $case->name;
        }

        return $keys;
    }

    /**
     * Get the label for a specific UUID type.
     */
    public function label(): string
    {
        return self::keysWithLabels()[$this->value] ?? $this->name;
    }
}
