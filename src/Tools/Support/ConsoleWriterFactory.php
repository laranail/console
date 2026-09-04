<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Simtabi\Laranail\Console\Tools\Services\ConsoleWriter;

/**
 * Builds {@see ConsoleWriter} instances; defaults to a fresh ConsoleOutput.
 *
 * @api
 */
final class ConsoleWriterFactory
{
    public function for(?OutputInterface $output = null): ConsoleWriter
    {
        return ConsoleWriter::make($output ?? new ConsoleOutput);
    }
}
