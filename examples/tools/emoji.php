<?php

declare(strict_types=1);

/*
 * Emoji: :shortcode: rendering, ASCII fallback, custom registration.
 *
 *   php examples/tools/emoji.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Tools\Support\Emoji;

$e = Emoji::make();
echo $e->render('Deploying :rocket:  Done :white_check_mark:  Careful :warning:'), "\n";
echo $e->ascii()->render('ASCII fallback: :rocket: :fire: :tada:'), "\n";
echo Emoji::make()->with(['deploy' => ['🛳️', '>>']])->render('Custom :deploy:'), "\n";
