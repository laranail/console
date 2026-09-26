<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

/**
 * The shared status vocabulary: one case per state a command commonly reports,
 * each carrying its glyph, colour, palette role and translated label, so a
 * consumer never hand-writes a `match` from a status string to a styled label.
 *
 * Glyphs come from {@see Symbols} (Unicode or ASCII per {@see Capabilities});
 * labels resolve through {@see Lang} under `laranail-console::console.status.*`
 * with an English fallback, so the enum works outside a booted application.
 *
 * @api Stable enum (SemVer-covered).
 */
enum Status: string
{
    case Success = 'success';
    case Failed = 'failed';
    case Warning = 'warning';
    case Pending = 'pending';
    case Running = 'running';
    case Skipped = 'skipped';
    case Active = 'active';
    case Inactive = 'inactive';
    case Unknown = 'unknown';

    /** English fallbacks, used when no translator is bound. */
    private const array DEFAULT_LABELS = [
        'success'  => 'Completed',
        'failed'   => 'Failed',
        'warning'  => 'Warning',
        'pending'  => 'Pending',
        'running'  => 'Processing',
        'skipped'  => 'Skipped',
        'active'   => 'Active',
        'inactive' => 'Inactive',
        'unknown'  => 'Unknown',
    ];

    public static function fromBool(bool $ok): self
    {
        return $ok ? self::Success : self::Failed;
    }

    /**
     * The glyph for this status, or '' when the status has none (Unknown).
     */
    public function symbol(?Capabilities $capabilities = null): string
    {
        $unicode = ($capabilities ?? Capabilities::detect())->symbolMode() === 'fancy';

        // Active/Inactive are on/off states, not messages, so they take a filled
        // and hollow dot rather than the `note` and `pending` symbols -- whose
        // ASCII forms (`[note]`, and the same `[ ]` as Pending) read wrong here.
        return match ($this) {
            self::Active   => $unicode ? '●' : '[on]',
            self::Inactive => $unicode ? '○' : '[off]',
            self::Unknown  => '',
            default        => Symbols::for($capabilities ?? Capabilities::detect())->get(match ($this) {
                self::Success => 'success',
                self::Failed  => 'error',
                self::Warning => 'warning',
                self::Pending => 'pending',
                self::Running => 'running',
                default       => 'skipped',
            }),
        };
    }

    /**
     * The Symfony formatter colour used for markup (`<fg=…>`).
     */
    public function color(): string
    {
        return match ($this) {
            self::Success, self::Active                  => 'green',
            self::Failed                                 => 'red',
            self::Warning, self::Running                 => 'yellow',
            self::Pending                                => 'blue',
            self::Skipped, self::Inactive, self::Unknown => 'gray',
        };
    }

    /**
     * The {@see \Simtabi\Laranail\Console\Tools\Theme\Palette} role, for widgets
     * that paint backgrounds (e.g. {@see \Simtabi\Laranail\Console\Tools\Widgets\Badge}).
     */
    public function role(): string
    {
        return match ($this) {
            self::Success, self::Active                  => 'success',
            self::Failed                                 => 'danger',
            self::Warning, self::Running                 => 'warning',
            self::Pending                                => 'info',
            self::Skipped, self::Inactive, self::Unknown => 'muted',
        };
    }

    public function label(): string
    {
        return Lang::get('status.' . $this->value, self::DEFAULT_LABELS[$this->value]);
    }
}
