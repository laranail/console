<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Benchmarks;

use PhpBench\Attributes as Bench;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;

/**
 * The hottest path in the toolkit — every cell/pad/wrap/truncate calls
 * DisplayWidth::of(). Guards the cached-formatter optimisation.
 */
#[Bench\Revs(20000)]
#[Bench\Iterations(5)]
#[Bench\Warmup(2)]
final class DisplayWidthBench
{
    private string $plain = 'the quick brown fox jumps over the lazy dog';

    private string $styled = "\033[1;31mthe quick\033[0m brown \033[34mfox\033[0m jumps";

    public function benchOfPlain(): void
    {
        DisplayWidth::of($this->plain);
    }

    public function benchOfStyled(): void
    {
        DisplayWidth::of($this->styled);
    }

    public function benchPad(): void
    {
        DisplayWidth::pad($this->plain, 60);
    }

    public function benchTruncateAnsi(): void
    {
        DisplayWidth::truncateAnsi($this->styled, 12);
    }
}
