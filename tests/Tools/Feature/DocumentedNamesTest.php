<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Feature;

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;

/**
 * Every publish tag, config key and published path the docs name must be one the booted package
 * actually registers. The docs kept the pre-namespacing names (`--tag=console-config`,
 * `config('console.*')`, `lang/fr/console.php`) for a month after the code moved, and a reader
 * following them got a silent no-op: an unknown tag publishes nothing, a bare key reads null, and
 * a file in the lang root is never read for a namespaced key.
 */
final class DocumentedNamesTest extends TestCase
{
    /** 31 pages on 2026-09-26; a glob that stops matching must fail, not pass empty. */
    private const int MIN_PAGES = 25;

    public function test_documented_publish_tags_exist(): void
    {
        $groups = ServiceProvider::publishableGroups();
        $seen = 0;

        foreach ($this->pages() as $page => $markdown) {
            preg_match_all('/--tag=([\w:.-]+)/', $markdown, $matches);

            foreach ($matches[1] as $tag) {
                $seen++;
                self::assertContains($tag, $groups, "{$page} documents --tag={$tag}, which the provider does not register.");
            }
        }

        self::assertGreaterThan(0, $seen, 'no --tag= found in the docs; the pattern no longer matches.');
    }

    public function test_documented_config_keys_are_the_registered_ones(): void
    {
        $seen = 0;

        foreach ($this->pages() as $page => $markdown) {
            preg_match_all("/config\\('([\\w.*]+)'/", $markdown, $matches);

            foreach ($matches[1] as $key) {
                if (! str_starts_with($key, 'laranail.console.') && ! str_starts_with($key, 'console.')) {
                    continue; // another package's or the application's key
                }

                $seen++;
                $resolvable = rtrim(str_replace('.*', '', $key), '.');

                self::assertStringStartsWith('laranail.console.', $key, "{$page} documents config('{$key}'), the pre-namespacing key.");
                self::assertTrue(config()->has($resolvable), "{$page} documents config('{$key}'), which the shipped config does not define.");
            }
        }

        self::assertGreaterThan(0, $seen, "no config('…') found in the docs; the pattern no longer matches.");
    }

    public function test_documented_paths_are_where_the_package_publishes(): void
    {
        $destinations = [];

        foreach (ServiceProvider::publishableGroups() as $group) {
            foreach (ServiceProvider::pathsToPublish(null, $group) as $to) {
                $destinations[] = $this->relative($to, $this->app->basePath());
            }
        }

        $seen = 0;

        foreach ($this->pages() as $page => $markdown) {
            // An application path, not one inside another path such as the package's resources/lang/en/.
            preg_match_all('#(?<![\w/])((?:config|lang)/[\w<>-]+(?:/[\w<>.-]*)*)#', $markdown, $matches);

            foreach ($matches[1] as $path) {
                $seen++;
                $published = array_filter($destinations, static fn (string $to): bool => str_starts_with($path, $to));

                self::assertNotSame([], $published, "{$page} names {$path}, which is not under any path the package publishes to.");
            }
        }

        self::assertGreaterThan(0, $seen, 'no config/ or lang/ path found in the docs; the pattern no longer matches.');
    }

    public function test_publish_destinations_compare_separator_insensitively(): void
    {
        // Windows joins with a backslash, so the docs' forward-slash paths must match either way.
        self::assertSame('config/laranail/console.php', $this->relative('C:\\app\\config\\laranail/console.php', 'C:\\app'));
        self::assertSame('config/laranail/console.php', $this->relative('/app/config/laranail/console.php', '/app'));
    }

    /**
     * A published destination relative to the application root, with forward slashes.
     */
    private function relative(string $path, string $base): string
    {
        $path = str_replace('\\', '/', $path);
        $base = rtrim(str_replace('\\', '/', $base), '/');

        return ltrim(str_starts_with($path, $base) ? substr($path, strlen($base)) : $path, '/');
    }

    /**
     * @return array<string, string> relative path => markdown
     */
    private function pages(): array
    {
        $root = dirname(__DIR__, 3);
        $files = [$root . '/README.md', ...(glob($root . '/docs/*.md') ?: []), ...(glob($root . '/docs/*/*.md') ?: [])];
        $pages = [];

        foreach ($files as $file) {
            $pages[substr($file, strlen($root) + 1)] = (string) file_get_contents($file);
        }

        self::assertGreaterThanOrEqual(self::MIN_PAGES, count($pages), 'too few doc pages found; the glob no longer matches.');

        return $pages;
    }
}
