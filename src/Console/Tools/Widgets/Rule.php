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

        // Keep the rule within the requested width: a title that can't fit
        // alongside at least two rule chars is truncated (titles are plain text).
        $title = $this->title;
        if (DisplayWidth::of($title) + 2 > $width - 2) {
            $title = rtrim(mb_substr($title, 0, max($width - 5, 0)));
        }

        $label = ' ' . $title . ' ';
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
