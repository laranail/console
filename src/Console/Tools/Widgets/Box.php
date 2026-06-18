<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\BorderStyle;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;

/**
 * Frames text in a box drawn from a single {@see BorderStyle} family.
 *
 * Every interior line is padded to one column width using {@see DisplayWidth}
 * so the frame never drifts, even with wide characters or ANSI colour. Mixing
 * glyph families is impossible by construction — all glyphs come from one enum
 * case.
 */
final class Box
{
    /** @var list<string> */
    private array $lines;

    private string $title = '';

    private string $footer = '';

    private BorderStyle $style;

    private int $padding = 1;

    private ?int $width = null;

    /**
     * @param list<string> $lines
     */
    public function __construct(array $lines = [], private readonly Capabilities $capabilities = new Capabilities())
    {
        $this->lines = $lines;
        $this->style = $this->capabilities->supportsUnicode() ? BorderStyle::Rounded : BorderStyle::Ascii;
    }

    /**
     * @param list<string>|string $lines
     */
    public static function make(array|string $lines = []): self
    {
        return new self(is_array($lines) ? $lines : explode("\n", $lines));
    }

    /**
     * @param list<string>|string $lines
     */
    public function content(array|string $lines): self
    {
        $this->lines = is_array($lines) ? $lines : explode("\n", $lines);

        return $this;
    }

    public function title(string $title): self
    {
        $this->title = ConsoleUIFormatter::sanitizeText($title);

        return $this;
    }

    public function footer(string $footer): self
    {
        $this->footer = ConsoleUIFormatter::sanitizeText($footer);

        return $this;
    }

    public function style(BorderStyle $style): self
    {
        $this->style = $style;

        return $this;
    }

    public function padding(int $padding): self
    {
        $this->padding = max($padding, 0);

        return $this;
    }

    public function width(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function rounded(): self
    {
        return $this->style(BorderStyle::Rounded);
    }

    public function double(): self
    {
        return $this->style(BorderStyle::Double);
    }

    public function heavy(): self
    {
        return $this->style(BorderStyle::Heavy);
    }

    public function render(): string
    {
        $g = $this->style->glyphs();
        $lines = array_map([ConsoleUIFormatter::class, 'sanitizeText'], $this->lines);

        $contentWidth = 0;
        foreach ([...$lines, $this->title, $this->footer] as $line) {
            $contentWidth = max($contentWidth, DisplayWidth::of($line));
        }

        $inner = ($this->width !== null ? max($this->width - 2, 1) : $contentWidth + ($this->padding * 2));
        $pad = str_repeat(' ', $this->padding);

        $top = $this->rule($g['tl'], $g['tr'], $g['h'], $inner, $this->title);
        $bottom = $this->rule($g['bl'], $g['br'], $g['h'], $inner, $this->footer);

        $body = [];
        foreach ($lines as $line) {
            $body[] = $g['v'] . $pad . DisplayWidth::pad($line, $inner - ($this->padding * 2)) . $pad . $g['v'];
        }

        return implode("\n", [$top, ...$body, $bottom]);
    }

    private function rule(string $left, string $right, string $h, int $inner, string $label): string
    {
        if ($label === '') {
            return $left . str_repeat($h, $inner) . $right;
        }

        $tagged = ' ' . $label . ' ';
        $fill = max($inner - DisplayWidth::of($tagged), 0);

        return $left . $h . $tagged . str_repeat($h, max($fill - 1, 0)) . $right;
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
