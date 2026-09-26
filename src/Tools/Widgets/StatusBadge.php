<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Stringable;
use Simtabi\Laranail\Console\Tools\Support\Status;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

/**
 * An inline, coloured status label — `✓ Completed`, `✗ Failed` — for a table
 * cell or a line of prose. Built from a {@see Status}, so the glyph, colour and
 * translated label are defined once rather than in every command's `match`.
 *
 * Domain states map onto the shared vocabulary with {@see fromMap()}, which keeps
 * the domain's own strings in the domain:
 *
 *     StatusBadge::fromMap(['up-to-date' => Status::Success], $state)->render();
 *
 * Returns Symfony Console **markup**; write it through an output (or place it in
 * a {@see Table} cell). Immutable: every modifier returns a new badge.
 *
 * @api Stable widget (SemVer-covered).
 */
final readonly class StatusBadge implements Stringable
{
    private function __construct(
        private Status $status,
        private ?string $label = null,
        private bool $symbol = true,
        private ?Capabilities $capabilities = null,
    ) {}

    public function __toString(): string
    {
        return $this->render();
    }

    public static function of(Status|bool $status): self
    {
        return new self($status instanceof Status ? $status : Status::fromBool($status));
    }

    /**
     * Badge for a domain value via a caller-owned map; an unmapped value renders
     * as {@see Status::Unknown} rather than failing, since the value usually comes
     * from stored state the command does not control.
     *
     * @param array<string, Status> $map
     */
    public static function fromMap(array $map, ?string $value): self
    {
        return new self($value !== null && isset($map[$value]) ? $map[$value] : Status::Unknown);
    }

    /**
     * Override the translated default label.
     */
    public function label(?string $label): self
    {
        return new self($this->status, $label, $this->symbol, $this->capabilities);
    }

    public function withoutSymbol(): self
    {
        return new self($this->status, $this->label, false, $this->capabilities);
    }

    public function capabilities(Capabilities $capabilities): self
    {
        return new self($this->status, $this->label, $this->symbol, $capabilities);
    }

    public function status(): Status
    {
        return $this->status;
    }

    public function render(): string
    {
        $text = ConsoleUIFormatter::sanitizeText($this->label ?? $this->status->label());
        $glyph = $this->symbol ? $this->status->symbol($this->capabilities) : '';

        return ConsoleUIFormatter::create()
            ->addMessage($glyph === '' ? $text : $glyph . ' ' . $text)
            ->addTextColor($this->status->color())
            ->render();
    }
}
