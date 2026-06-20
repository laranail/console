<?php

declare(strict_types=1);

/*
 * Demonstrates the Prompter validators without prompting (no TTY required).
 *
 *   php examples/prompter/validators.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Simtabi\Laranail\Console\Prompter\Validators\EmailFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\PathFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\SelectFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\UUIDFieldValidator;

$checks = [
    'email (good)' => [new EmailFieldValidator('invalid email'), 'dev@simtabi.com'],
    'email (bad)' => [new EmailFieldValidator('invalid email'), 'nope'],
    'email (non-string)' => [new EmailFieldValidator('invalid email'), ['array']],
    'path (good)' => [new PathFieldValidator('invalid path'), '/usr/local/bin'],
    'path (traversal)' => [new PathFieldValidator('invalid path'), '../etc/passwd'],
    'uuid (v4)' => [new UUIDFieldValidator('invalid uuid'), 'f47ac10b-58cc-4372-a567-0e02b2c3d479'],
    'select (allowed)' => [new SelectFieldValidator(['mysql', 'pgsql'], 'invalid choice'), 'pgsql'],
    'select (juggle)' => [new SelectFieldValidator(['mysql', 'pgsql'], 'invalid choice'), 0],
];

foreach ($checks as $name => [$validator, $value]) {
    $error = $validator->validate($value);
    $label = str_pad($name, 22);
    echo $error === null ? "  OK   {$label}\n" : "  FAIL {$label} → {$error}\n";
}
