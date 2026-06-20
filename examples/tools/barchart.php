<?php

declare(strict_types=1);

/*
 * A responsive, themed horizontal bar chart.
 *
 *   php examples/tools/barchart.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Widgets\BarChart;

echo BarChart::make([
    'API' => 1240,
    'Web' => 860,
    'CLI' => 320,
    'Queue' => 95,
    'Cron' => 12,
])->render(), "\n";
