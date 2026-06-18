<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Simtabi\Laranail\Console\Tools\Support\Capabilities;

/**
 * An inline mini-chart built from block-eighths (`▁▂▃▄▅▆▇█`).
 *
 * Unicode-only: without it there is no faithful ASCII equivalent, so the
 * sparkline degrades to a compact numeric summary.
 */
final class Sparkline
{
    private const TICKS = ['▁', '▂', '▃', '▄', '▅', '▆', '▇', '█'];

    private readonly bool $unicode;

    /**
     * @param list<int|float> $values
     */
    public function __construct(private readonly array $values, ?Capabilities $capabilities = null)
    {
        $this->unicode = ($capabilities ?? Capabilities::detect())->supportsUnicode();
    }

    /**
     * @param list<int|float> $values
     */
    public static function make(array $values): self
    {
        return new self($values);
    }

    public function render(): string
    {
        if ($this->values === []) {
            return '';
        }

        $min = min($this->values);
        $max = max($this->values);
        $span = ($max - $min) ?: 1;

        if (! $this->unicode) {
            return sprintf('min %s, max %s, n %d', $this->trim($min), $this->trim($max), count($this->values));
        }

        $out = '';
        foreach ($this->values as $value) {
            $level = (int) round((($value - $min) / $span) * (count(self::TICKS) - 1));
            $out .= self::TICKS[$level];
        }

        return $out;
    }

    private function trim(int|float $n): string
    {
        return rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
