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

    /** Takes the grapheme-cluster path: a ZWJ family, a flag and a VS16 sequence. */
    private string $emoji = "deploy \u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467} to \u{1F1F0}\u{1F1EA} \u{2139}\u{FE0F} done";

    public function benchOfPlain(): void
    {
        DisplayWidth::of($this->plain);
    }

    public function benchOfStyled(): void
    {
        DisplayWidth::of($this->styled);
    }

    public function benchOfEmojiSequences(): void
    {
        DisplayWidth::of($this->emoji);
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
