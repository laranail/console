<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Commands\Concerns;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\warning;

use Simtabi\Laranail\Console\Tools\Support\Lang;

/**
 * Confirmation for an irreversible action, in one place:
 *
 * - `--force`, when the command declares it, answers yes **without prompting**;
 * - otherwise the user is asked, defaulting to "no";
 * - {@see cancelled()} reports the refusal and returns a success exit code,
 *   since declining is not a failure.
 *
 *     if (! $this->confirmDestructive('Drop every icon table?')) {
 *         return $this->cancelled();
 *     }
 *
 * The `--force` check is guarded by `hasOption()`, so a command that does not
 * declare the option still works rather than throwing on an undefined option.
 *
 * @api Stable trait (SemVer-covered).
 */
trait ConfirmsDestructiveActions
{
    protected function confirmDestructive(string $question, ?string $hint = null): bool
    {
        if ($this->forced()) {
            return true;
        }

        return confirm(
            label: $question,
            default: false,
            yes: Lang::get('confirm.yes', 'Yes, proceed'),
            no: Lang::get('confirm.no', 'No, cancel'),
            hint: $hint ?? Lang::get('confirm.irreversible', 'This action cannot be undone.'),
        );
    }

    protected function cancelled(?string $message = null): int
    {
        warning($message ?? Lang::get('confirm.cancelled', 'Operation cancelled.'));

        return self::SUCCESS;
    }

    protected function forced(): bool
    {
        return $this->hasOption('force') && (bool) $this->option('force');
    }
}
