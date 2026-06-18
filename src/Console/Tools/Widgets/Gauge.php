<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;

/**
 * A single-value horizontal gauge/meter, e.g. `Disk  [██████░░] 72% (180/250)`.
 */
final class Gauge
{
    private string $label = '';

    private int $barWidth = 20;

    private bool $showValue = false;

    private readonly bool $unicode;

    public function __construct(private readonly float $value, private readonly float $max = 100.0, ?Capabilities $capabilities = null)
    {
        $this->unicode = ($capabilities ?? Capabilities::detect())->supportsUnicode();
    }

    public static function make(float $value, float $max = 100.0): self
    {
        return new self($value, $max);
    }

    public function label(string $label): self
    {
        $this->label = ConsoleUIFormatter::sanitizeText($label);

        return $this;
    }

    public function width(int $barWidth): self
    {
        $this->barWidth = max($barWidth, 1);

        return $this;
    }

    public function showValue(bool $show = true): self
    {
        $this->showValue = $show;

        return $this;
    }

    public function render(): string
    {
        $ratio = $this->max > 0 ? max(0.0, min(1.0, $this->value / $this->max)) : 0.0;
        $filled = (int) round($ratio * $this->barWidth);

        [$full, $empty] = $this->unicode ? ['█', '░'] : ['#', '-'];
        $bar = str_repeat($full, $filled) . str_repeat($empty, $this->barWidth - $filled);

        $percent = (int) round($ratio * 100);
        $out = ($this->label !== '' ? $this->label . '  ' : '') . "[{$bar}] {$percent}%";

        if ($this->showValue) {
            $out .= sprintf(' (%s/%s)', $this->trim($this->value), $this->trim($this->max));
        }

        return $out;
    }

    private function trim(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
