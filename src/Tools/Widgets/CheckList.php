<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Stringable;
use Simtabi\Laranail\Console\Tools\Support\Lang;
use Simtabi\Laranail\Console\Tools\Support\Status;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

/**
 * A readiness checklist: one aligned row per check, a glyph, the label, and a
 * status word — the `✓ Database: OK / ✗ Cache: NOT READY` block a diagnostic
 * command prints.
 *
 *     CheckList::make('Readiness')
 *         ->check('Tables', true)
 *         ->check('Seeded', false, 'run the seed command')
 *         ->render();
 *
 * A boolean check reads as the pass/fail words ({@see passLabel()} /
 * {@see failLabel()}, translated, "OK" / "NOT READY" by default); a
 * {@see Status} check reads as that status's own label. Labels are padded to
 * the widest one (display-width aware). Returns Symfony Console **markup**.
 *
 * @api Stable widget (SemVer-covered).
 */
final class CheckList implements Stringable
{
    /** @var list<array{label: string, status: Status, text: ?string, detail: ?string}> */
    private array $checks = [];

    private ?string $passLabel = null;

    private ?string $failLabel = null;

    private readonly Capabilities $capabilities;

    public function __construct(private readonly ?string $title = null, ?Capabilities $capabilities = null)
    {
        $this->capabilities = $capabilities ?? Capabilities::detect();
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public static function make(?string $title = null, ?Capabilities $capabilities = null): self
    {
        return new self($title, $capabilities);
    }

    public function check(string $label, bool|Status $state, ?string $detail = null): self
    {
        $this->checks[] = [
            'label'  => ConsoleUIFormatter::sanitizeText($label),
            'status' => $state instanceof Status ? $state : Status::fromBool($state),
            'text'   => is_bool($state) ? ($state ? 'pass' : 'fail') : null,
            'detail' => $detail === null ? null : ConsoleUIFormatter::sanitizeText($detail),
        ];

        return $this;
    }

    public function passLabel(string $label): self
    {
        $this->passLabel = $label;

        return $this;
    }

    public function failLabel(string $label): self
    {
        $this->failLabel = $label;

        return $this;
    }

    /**
     * True when every check reads as passing: Success, Skipped or Active.
     */
    public function passes(): bool
    {
        return array_all($this->checks, fn (array $check): bool => in_array($check['status'], [Status::Success, Status::Skipped, Status::Active], true));
    }

    public function render(): string
    {
        if ($this->checks === []) {
            return '';
        }

        $width = DisplayWidth::maxWidth(array_map(static fn (array $c): string => $c['label'] . ':', $this->checks));
        $lines = [];

        if ($this->title !== null) {
            $lines[] = '<options=bold>' . ConsoleUIFormatter::sanitizeText($this->title) . '</>';
        }

        foreach ($this->checks as $check) {
            $status = $check['status'];
            $word = match ($check['text']) {
                'pass'  => $this->passLabel ?? Lang::get('widgets.check_list.pass', 'OK'),
                'fail'  => $this->failLabel ?? Lang::get('widgets.check_list.fail', 'NOT READY'),
                default => $status->label(),
            };

            $glyph = $status->symbol($this->capabilities);
            $label = $check['label'] . ':';
            $padded = $label . str_repeat(' ', max(0, $width - DisplayWidth::of($label)));

            $line = '  ' . ($glyph === '' ? '' : "<fg={$status->color()}>{$glyph}</> ")
                . $padded . ' '
                . "<fg={$status->color()}>" . ConsoleUIFormatter::sanitizeText($word) . '</>';

            if ($check['detail'] !== null) {
                $line .= ' <fg=gray>' . $check['detail'] . '</>';
            }

            $lines[] = $line;
        }

        return implode(PHP_EOL, $lines);
    }
}
