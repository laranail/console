<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Stringable;
use Simtabi\Laranail\Console\Tools\Support\Status;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\NumberFormat;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

/**
 * A single-value horizontal gauge/meter, e.g. `Disk  [██████░░] 72% (180/250)`.
 *
 * Plain text by default, so it can be echoed anywhere. {@see color()} or
 * {@see Status()} colours the filled segment, and the result then carries
 * Symfony Console markup: write it through an output (or place it in a
 * {@see Table} cell), the same contract as {@see StatusBadge}.
 */
final class Gauge implements Stringable
{
    private string $label = '';

    private int $barWidth = 20;

    private bool $showValue = false;

    private ?string $color = null;

    private readonly bool $unicode;

    public function __construct(private readonly float $value, private readonly float $max = 100.0, ?Capabilities $capabilities = null)
    {
        $this->unicode = ($capabilities ?? Capabilities::detect())->supportsUnicode();
    }

    public function __toString(): string
    {
        return $this->render();
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

    public function width(int $width): self
    {
        $this->barWidth = max($width, 1);

        return $this;
    }

    public function showValue(bool $show = true): self
    {
        $this->showValue = $show;

        return $this;
    }

    /**
     * Colour the filled segment with a Symfony formatter colour (`green`,
     * `yellow`, …). Null restores plain output.
     */
    public function color(?string $color): self
    {
        $this->color = $color === null ? null : ConsoleUIFormatter::sanitizeText($color);

        return $this;
    }

    /**
     * Colour the filled segment by a {@see Status}, so a progress bar reads the
     * same as the status badge beside it.
     */
    public function status(Status $status): self
    {
        return $this->color($status->color());
    }

    public function render(): string
    {
        $ratio = $this->max > 0 ? max(0.0, min(1.0, $this->value / $this->max)) : 0.0;
        $percent = (int) round($ratio * 100);
        $filled = (int) round($ratio * $this->barWidth);

        // Don't show a full bar unless we're actually at 100% (rounding could
        // otherwise fill the bar at, say, 99%).
        if ($filled === $this->barWidth && $percent < 100) {
            $filled = $this->barWidth - 1;
        }

        [$full, $empty] = $this->unicode ? ['█', '░'] : ['#', '-'];
        $done = str_repeat($full, $filled);
        $bar = ($this->color !== null && $done !== '' ? "<fg={$this->color}>{$done}</>" : $done)
            . str_repeat($empty, $this->barWidth - $filled);
        $out = ($this->label !== '' ? $this->label . '  ' : '') . "[{$bar}] {$percent}%";

        if ($this->showValue) {
            $out .= sprintf(' (%s/%s)', $this->trim($this->value), $this->trim($this->max));
        }

        return $out;
    }

    private function trim(float $n): string
    {
        return NumberFormat::trim($n);
    }
}
