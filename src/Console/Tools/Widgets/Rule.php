<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\BorderStyle;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Stringable;

/**
 * A full-width horizontal divider with an optional inline title.
 */
final class Rule implements Stringable
{
    private string $title = '';

    private BorderStyle $style;

    private ?int $width = null;

    private string $align = 'left';

    public function __construct(private readonly Capabilities $capabilities = new Capabilities)
    {
        $this->style = $this->capabilities->supportsUnicode() ? BorderStyle::Light : BorderStyle::Ascii;
    }

    public static function make(string $title = ''): self
    {
        return (new self)->title($title);
    }

    public function title(string $title): self
    {
        $this->title = ConsoleUIFormatter::sanitizeText($title);

        return $this;
    }

    public function style(BorderStyle $style): self
    {
        $this->style = $style;

        return $this;
    }

    public function width(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function center(): self
    {
        $this->align = 'center';

        return $this;
    }

    public function render(): string
    {
        $width = $this->width ?? $this->capabilities->width();
        $line = $this->style->glyphs()['h'];

        if ($this->title === '') {
            return str_repeat($line, max($width, 1));
        }

        $label = ' ' . $this->title . ' ';
        $labelWidth = DisplayWidth::of($label);
        $remaining = max($width - $labelWidth, 2);

        if ($this->align === 'center') {
            $left = intdiv($remaining, 2);

            return str_repeat($line, $left) . $label . str_repeat($line, $remaining - $left);
        }

        return $line . $line . $label . str_repeat($line, max($remaining - 2, 0));
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
