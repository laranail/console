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

    public function test_intra_repo_fragment_links_resolve(): void
    {
        // getting-started linked ../README.md#writing-through-an-output for months: the file
        // resolves, only the fragment is wrong, and no link checker reports that.
        $root = dirname(__DIR__, 3);
        $seen = 0;

        foreach ($this->pages() as $page => $markdown) {
            preg_match_all('/\]\(([^)\s#]*)#([^)\s]+)\)/', $this->withoutFences($markdown), $matches, PREG_SET_ORDER);

            foreach ($matches as [, $path, $fragment]) {
                if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $path) === 1) {
                    continue; // an external URL
                }

                $seen++;
                $target = $path === '' ? $root . '/' . $page : dirname($root . '/' . $page) . '/' . $path;

                self::assertFileExists($target, "{$page} links {$path}#{$fragment}, whose file does not exist.");
                self::assertContains(
                    $fragment,
                    $this->anchors((string) file_get_contents($target)),
                    "{$page} links {$path}#{$fragment}, but that page has no such heading or anchor.",
                );
            }
        }

        // 62 on 2026-10-04; a pattern that stops matching must fail, not pass empty.
        self::assertGreaterThanOrEqual(40, $seen, 'too few fragment links found; the pattern no longer matches.');
    }

    public function test_the_anchor_slugger_matches_github(): void
    {
        // The explicit <a name> and the heading slug both yield "documentation"; a fenced line is skipped.
        self::assertSame(
            ['quick-start-guide-and-usage', 'documentation', 'documentation', 'contributing--security', 'laranailconsolecheck', 'usage', 'usage-1'],
            $this->anchors("## Quick start guide and usage\n## <a name=\"documentation\"></a>Documentation\n## Contributing & security\n## `laranail::console.check`\n### Usage\n### Usage\n```\n# not a heading\n```\n"),
        );
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
     * Every anchor a page exposes: GitHub's heading slugs (duplicates suffixed -1, -2, ...)
     * plus explicit `<a name>` / `<a id>` targets. Headings inside code fences are not headings.
     *
     * @return list<string>
     */
    private function anchors(string $markdown): array
    {
        $anchors = [];
        $counts = [];

        foreach (explode("\n", $this->withoutFences($markdown)) as $line) {
            preg_match_all('/<a\s+(?:name|id)="([^"]+)"/', $line, $explicit);

            foreach ($explicit[1] as $name) {
                $anchors[] = $name;
            }

            if (preg_match('/^#{1,6}\s+(.*?)\s*#*\s*$/', $line, $heading) !== 1) {
                continue;
            }

            $text = str_replace('`', '', (string) preg_replace('/<[^>]+>/', '', $heading[1]));
            $slug = str_replace(' ', '-', (string) preg_replace('/[^\p{L}\p{N}_\- ]/u', '', mb_strtolower(trim($text))));
            $n = $counts[$slug] ?? 0;
            $counts[$slug] = $n + 1;
            $anchors[] = $n === 0 ? $slug : "{$slug}-{$n}";
        }

        return $anchors;
    }

    private function withoutFences(string $markdown): string
    {
        return (string) preg_replace('/^[ \t]*```.*?^[ \t]*```[^\n]*$/ms', '', $markdown);
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
