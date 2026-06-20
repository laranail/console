<?php

declare(strict_types=1);

/*
 * Symbols: capability-aware glyphs (Unicode or ASCII fallback).
 *
 *   php examples/tools/symbols.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Symbols;

$s = Symbols::for(Capabilities::detect());
foreach (['arrow', 'success', 'warning', 'branch', 'last', 'bullet', 'pointer'] as $name) {
    echo str_pad($name, 10), $s->get($name), "\n";
}
