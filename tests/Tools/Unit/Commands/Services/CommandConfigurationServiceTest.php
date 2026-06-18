<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Commands\Services;

use Simtabi\Laranail\Console\Tools\Commands\Services\CommandConfigurationService;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;

final class CommandConfigurationServiceTest extends TestCase
{
    public function test_get_returns_default_for_missing_key(): void
    {
        $service = new CommandConfigurationService;

        self::assertSame('fallback', $service->get('does.not.exist', 'fallback'));
    }

    public function test_get_reads_from_laravel_config(): void
    {
        config()->set('some.driver', 'redis');

        self::assertSame('redis', (new CommandConfigurationService)->get('some.driver'));
    }

    public function test_get_env_prefixes_the_app_namespace(): void
    {
        config()->set('app.timezone', 'UTC');

        self::assertSame('UTC', (new CommandConfigurationService)->getEnv('timezone'));
    }

    public function test_get_caches_resolved_values(): void
    {
        config()->set('cache.me', 'first');
        $service = new CommandConfigurationService;

        self::assertSame('first', $service->get('cache.me'));
        self::assertArrayHasKey('cache.me', $service->getAllCached());

        // Underlying config changes; cached value is returned instead.
        config()->set('cache.me', 'second');
        self::assertSame('first', $service->get('cache.me'));
    }

    public function test_set_overrides_cache_and_clear_cache_resets(): void
    {
        $service = new CommandConfigurationService;

        self::assertSame($service, $service->set('runtime.key', 'val'));
        self::assertSame('val', $service->get('runtime.key'));

        $service->clearCache();
        self::assertSame([], $service->getAllCached());
    }

    public function test_has_reflects_config_presence(): void
    {
        config()->set('present.key', 'x');
        $service = new CommandConfigurationService;

        self::assertTrue($service->has('present.key'));
        self::assertFalse($service->has('absent.key'));
    }
}
