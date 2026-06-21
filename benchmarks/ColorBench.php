<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Benchmarks;

use PhpBench\Attributes as Bench;
use Simtabi\Laranail\Console\Tools\Support\Color;

/**
 * Colour parsing/blending — used by themes, charts (heatmap blend) and every
 * gradient. Framework-free (static, no container).
 */
#[Bench\Revs(20000)]
#[Bench\Iterations(5)]
#[Bench\Warmup(2)]
final class ColorBench
{
    public function benchParseHex(): void
    {
        Color::parse('#7c3aed');
    }

    public function benchParseNamed(): void
    {
        Color::parse('slate');
    }

    public function benchBlend(): void
    {
        Color::blend('#ff0000', '#0000ff', 0.5);
    }
}
