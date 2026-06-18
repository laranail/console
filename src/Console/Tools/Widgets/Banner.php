<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;

/**
 * A start-of-run masthead: a centred title with optional subtitle, rendered as
 * a rule-wrapped block or a framed box.
 */
final class Banner
{
    private string $subtitle = '';

    private bool $boxed = false;

    private ?int $width = null;

    public function __construct(private string $title, private readonly Capabilities $capabilities = new Capabilities())
    {
        $this->title = ConsoleUIFormatter::sanitizeText($title);
    }

    public static function make(string $title): self
    {
        return new self($title);
    }

    public function subtitle(string $subtitle): self
    {
        $this->subtitle = ConsoleUIFormatter::sanitizeText($subtitle);

        return $this;
    }

    public function boxed(bool $boxed = true): self
    {
        $this->boxed = $boxed;

        return $this;
    }

    public function width(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function render(): string
    {
        $width = $this->width ?? min($this->capabilities->width(), 60);
        $lines = [DisplayWidth::center($this->title, $width)];

        if ($this->subtitle !== '') {
            $lines[] = DisplayWidth::center($this->subtitle, $width);
        }

        if ($this->boxed) {
            return Box::make($lines)->width($width + 4)->padding(1)->render();
        }

        $rule = (new Rule($this->capabilities))->width($width)->render();

        return implode("\n", [$rule, ...$lines, $rule]);
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
