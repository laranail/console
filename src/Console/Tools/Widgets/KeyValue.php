<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Stringable;

/**
 * A definition list: aligned `key : value` pairs. Keys are padded to the widest
 * key (display-width aware); all input is sanitised. Returns an echo-safe string.
 */
final class KeyValue implements Stringable
{
    /** @var array<string, string> */
    private array $pairs;

    private string $separator = ':';

    /**
     * @param array<string, scalar|null> $pairs
     */
    public function __construct(array $pairs = [])
    {
        $this->pairs = $this->normalize($pairs);
    }

    /**
     * @param array<string, scalar|null> $pairs
     */
    public static function make(array $pairs = []): self
    {
        return new self($pairs);
    }

    public function add(string $key, int|float|string|bool|null $value): self
    {
        $this->pairs[ConsoleUIFormatter::sanitizeText($key)] = ConsoleUIFormatter::sanitizeText((string) $value);

        return $this;
    }

    public function separator(string $separator): self
    {
        $this->separator = ConsoleUIFormatter::sanitizeText($separator);

        return $this;
    }

    public function render(): string
    {
        if ($this->pairs === []) {
            return '';
        }

        $width = 0;
        foreach (array_keys($this->pairs) as $key) {
            $width = max($width, DisplayWidth::of($key));
        }

        $lines = [];
        foreach ($this->pairs as $key => $value) {
            $lines[] = DisplayWidth::pad($key, $width) . ' ' . $this->separator . ' ' . $value;
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string, scalar|null> $pairs
     * @return array<string, string>
     */
    private function normalize(array $pairs): array
    {
        $normalized = [];

        foreach ($pairs as $key => $value) {
            $normalized[ConsoleUIFormatter::sanitizeText((string) $key)] = ConsoleUIFormatter::sanitizeText((string) $value);
        }

        return $normalized;
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
