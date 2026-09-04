<?php

declare(strict_types=1);

/*
 * Colour + style demo: parsing (hex/rgb/hsl/named/@256), graceful downgrade
 * (truecolor → 256 → 16 → none), gradient, blend, adaptive, and the fluent Style.
 *
 *   php examples/tools/colors.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Support\Color;
use Simtabi\Laranail\Console\Tools\Support\Style;

$color = Color::make();

echo $color->fg('hex     #7c3aed', '#7c3aed'), "\n";
echo $color->fg('rgb     10,120,255', 'rgb(10,120,255)'), "\n";
echo $color->fg('hsl     280,90%,60%', 'hsl(280,90%,60%)'), "\n";
echo $color->fg('named   crimson', 'crimson'), "\n";
echo $color->fg('xterm   @196', '@196'), "\n";
echo $color->bg('  background  ', '#1e293b'), "\n\n";

echo $color->gradient('gradient across a sentence of colour', ['#06b6d4', '#7c3aed', '#ec4899']), "\n";
echo 'blend 50% of red+blue = ' . Color::blend('#ff0000', '#0000ff', 0.5), "\n";
echo 'adaptive (by background) = ' . Color::adaptive('#000000', '#ffffff'), "\n\n";

echo Style::make()->fg('#16a34a')->bold()->apply('bold green'), '  ';
echo Style::make()->fg('#d97706')->italic()->underline()->apply('italic underline'), '  ';
echo Style::make()->bg('#dc2626')->fg('#ffffff')->apply(' inverse-ish '), "\n";
