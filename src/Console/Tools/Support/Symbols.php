<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

/**
 * A single source of glyphs (status icons, tree connectors) with Unicode and
 * ASCII variants. The variant is chosen once from {@see Capabilities} so the
 * rest of the package never sprinkles conditional "is Unicode supported?" checks.
 */
final readonly class Symbols
{
    private const array FANCY = [
        'success' => '✓',
        'error' => '✗',
        'warning' => '⚠',
        'info' => 'ℹ',
        'pending' => '○',
        'running' => '◉',
        'bullet' => '•',
        'arrow' => '→',
        'branch' => '├─',
        'last' => '└─',
        'stem' => '│ ',
        'gap' => '  ',
    ];

    private const array ASCII = [
        'success' => '[OK]',
        'error' => '[X]',
        'warning' => '[!]',
        'info' => '[i]',
        'pending' => '[ ]',
        'running' => '[*]',
        'bullet' => '*',
        'arrow' => '->',
        'branch' => '|-',
        'last' => '\\-',
        'stem' => '| ',
        'gap' => '  ',
    ];

    public function __construct(private bool $unicode) {}

    public static function for(Capabilities $capabilities): self
    {
        return new self($capabilities->supportsUnicode());
    }

    public static function fancy(): self
    {
        return new self(true);
    }

    public static function ascii(): self
    {
        return new self(false);
    }

    public function get(string $name): string
    {
        $set = $this->unicode ? self::FANCY : self::ASCII;

        return $set[$name] ?? '';
    }

    public function usesUnicode(): bool
    {
        return $this->unicode;
    }
}
