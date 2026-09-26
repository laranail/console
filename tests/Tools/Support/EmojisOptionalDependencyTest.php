<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;

/**
 * Console is a foundation package: `laranail/emojis` requires console, so console may only suggest it.
 */
final class EmojisOptionalDependencyTest extends TestCase
{
    public function test_laranail_emojis_is_suggested_and_never_required(): void
    {
        /** @var array{require: array<string, string>, require-dev: array<string, string>, suggest: array<string, string>} $composer */
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 3) . '/composer.json'), true, flags: JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('laranail/emojis', $composer['suggest']);
        self::assertArrayNotHasKey('laranail/emojis', $composer['require']);
        self::assertArrayNotHasKey('laranail/emojis', $composer['require-dev']);
    }
}
