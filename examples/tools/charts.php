<?php

declare(strict_types=1);

/*
 * Charts: column, line (braille), scatter (braille), heatmap and histogram.
 * All responsive + themed; degrade to ASCII/shade output without colour/Unicode.
 *
 *   php examples/tools/charts.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Widgets\ColumnChart;
use Simtabi\Laranail\Console\Tools\Widgets\Heatmap;
use Simtabi\Laranail\Console\Tools\Widgets\Histogram;
use Simtabi\Laranail\Console\Tools\Widgets\LineChart;
use Simtabi\Laranail\Console\Tools\Widgets\ScatterPlot;

echo "Column chart\n";
echo ColumnChart::make(['Mon' => 12, 'Tue' => 19, 'Wed' => 7, 'Thu' => 22, 'Fri' => 15])->height(6)->render(), "\n\n";

echo "Line chart (two series)\n";
echo LineChart::make([
    'api' => [12, 18, 9, 22, 16, 28, 20, 31, 24],
    'web' => [8, 11, 14, 10, 17, 13, 21, 18, 26],
])->height(6)->render(), "\n\n";

echo "Scatter plot\n";
echo ScatterPlot::make([[1, 2], [2, 5], [3, 4], [4, 9], [5, 7], [6, 12], [7, 10]])->height(6)->render(), "\n\n";

echo "Heatmap\n";
echo Heatmap::make([
    [1, 2, 3, 4, 5],
    [2, 4, 6, 8, 10],
    [5, 4, 3, 2, 1],
])->labels(['low', 'mid', 'high'])->cellWidth(3)->render(), "\n\n";

echo "Histogram\n";
echo Histogram::make([1, 2, 2, 3, 3, 3, 4, 4, 4, 4, 5, 5, 6, 7, 8, 9, 9])->height(6)->render(), "\n";
