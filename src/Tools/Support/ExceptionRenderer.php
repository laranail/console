<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

use Throwable;
use Symfony\Component\Console\Output\OutputInterface;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;

/**
 * Writes a caught exception to the console at the right level of detail:
 *
 * - always the message, prefixed by an optional context ("Cache rebuild failed");
 * - file and line from `-v`;
 * - the stack trace only from `-vvv`.
 *
 * Traces can carry sensitive call arguments — credentials passed to a connector,
 * tokens in a URL — so they are never shown at lower verbosity. This is the one
 * place that decision lives; {@see \Simtabi\Laranail\Console\Tools\Commands\Concerns\InteractsWithConsoleServices::handleException()}
 * delegates here.
 *
 *     ExceptionRenderer::make($this->output)->context('Import failed')->render($e);
 *
 * @api Stable support class (SemVer-covered).
 */
final readonly class ExceptionRenderer
{
    private function __construct(
        private OutputInterface $output,
        private ?string $context = null,
    ) {}

    public static function make(OutputInterface $output): self
    {
        return new self($output);
    }

    public function context(?string $context): self
    {
        return new self($this->output, $context);
    }

    public function render(Throwable $e): void
    {
        $message = ConsoleUIFormatter::sanitizeText($e->getMessage());
        $prefix = $this->context ?? Lang::get('exception.failed', 'Command failed');

        $this->output->writeln('<error>' . ConsoleUIFormatter::sanitizeText($prefix) . ': ' . $message . '</error>');

        if ($this->output->isVerbose()) {
            $this->output->writeln(Lang::get('exception.file', 'File: :location', ['location' => $e->getFile() . ':' . $e->getLine()]));
        }

        if ($this->output->isDebug()) {
            $this->output->writeln(Lang::get('exception.trace', 'Trace: :trace', ['trace' => $e->getTraceAsString()]));
        }
    }
}
