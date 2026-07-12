<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Commands\Services;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Commands\Services\CommandMetadataService;

final class CommandMetadataServiceTest extends TestCase
{
    public function test_add_get_has_count(): void
    {
        $meta = new CommandMetadataService;
        $meta->add('foo', 'bar')->add('n', 42);

        self::assertTrue($meta->has('foo'));
        self::assertSame('bar', $meta->get('foo'));
        self::assertSame(42, $meta->get('n'));
        self::assertSame(2, $meta->count());
        self::assertFalse($meta->isEmpty());
    }

    public function test_remove_and_clear(): void
    {
        $meta = new CommandMetadataService;
        $meta->addMany(['a' => 1, 'b' => 2]);

        $meta->remove('a');
        self::assertFalse($meta->has('a'));
        self::assertSame(['b' => 2], $meta->all());

        $meta->clear();
        self::assertTrue($meta->isEmpty());
    }
}
