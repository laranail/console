<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Feature;

use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Translations;
use Simtabi\Laranail\Console\Exceptions\ConsoleException;
use Simtabi\Laranail\Console\Providers\ConsoleServiceProvider;
use Simtabi\Laranail\Console\Prompter\Validators\EmailFieldValidator;

/**
 * The package's translation namespaces, read from the live translator rather than the provider
 * source: `laranail/console` is canonical, `laranail-console` is the pre-0.1.5 name kept as a
 * deprecated alias over the same files, and the package's own lookups honour an override a host
 * made against either.
 */
final class TranslationNamespaceTest extends TestCase
{
    private const string LANG_PATH = __DIR__ . '/../../../resources/lang';

    public function test_both_namespaces_are_registered_over_the_same_files(): void
    {
        $hints = Lang::getLoader()->namespaces();

        self::assertArrayHasKey('laranail/console', $hints);
        self::assertArrayHasKey('laranail-console', $hints);
        self::assertSame(realpath(self::LANG_PATH), realpath($hints['laranail/console']));
        self::assertSame(realpath($hints['laranail/console']), realpath($hints['laranail-console']));
    }

    public function test_no_bare_namespace_is_registered(): void
    {
        self::assertArrayNotHasKey('console', Lang::getLoader()->namespaces());
    }

    public function test_both_forms_resolve_the_same_string(): void
    {
        self::assertSame('Invalid input. Please try again.', __('laranail/console::console.invalid_input'));
        self::assertSame(__('laranail/console::console.invalid_input'), __('laranail-console::console.invalid_input'));
        self::assertSame(__('laranail/console::validators.email'), __('laranail-console::validators.email'));
    }

    public function test_the_package_resolves_through_the_canonical_namespace(): void
    {
        $this->app['translator']->addLines(['console.invalid_input' => 'canonical override'], 'en', 'laranail/console');

        self::assertSame('canonical override', Translations::get('console.invalid_input'));
    }

    public function test_an_override_made_against_the_old_namespace_still_applies(): void
    {
        // A host that published to lang/vendor/laranail-console, or added lines to it, before 0.1.5.
        $this->app['translator']->addLines(['validators.email' => 'legacy override'], 'en', 'laranail-console');

        self::assertSame('legacy override', Translations::get('validators.email'));
        self::assertSame('legacy override', new EmailFieldValidator()->validate('nope'));
    }

    public function test_the_canonical_override_wins_when_both_are_overridden(): void
    {
        $this->app['translator']->addLines(['console.invalid_input' => 'legacy'], 'en', 'laranail-console');
        $this->app['translator']->addLines(['console.invalid_input' => 'canonical'], 'en', 'laranail/console');

        self::assertSame('canonical', Translations::get('console.invalid_input'));
    }

    public function test_replacements_apply_to_either_source(): void
    {
        $this->app['translator']->addLines(['console.custom' => 'hello :name'], 'en', 'laranail-console');

        self::assertSame('hello Ada', Translations::get('console.custom', ['name' => 'Ada']));
    }

    public function test_a_missing_key_returns_the_canonical_key(): void
    {
        self::assertSame('laranail/console::console.no_such_key', Translations::get('console.no_such_key'));
        self::assertSame('No such key', ConsoleException::fromKey('console.no_such_key')->getMessage());
    }

    public function test_no_internal_lookup_uses_the_old_namespace(): void
    {
        // Source, not behaviour: a lookup that slips back to the hyphen form still resolves, so
        // nothing else would notice it bypassing Translations::get().
        $root = dirname(__DIR__, 3) . '/src';
        $files = 0;

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $files++;

            // String literals only (plain and interpolated), so a docblock naming the old
            // namespace for a reader is not mistaken for a lookup.
            foreach (token_get_all((string) file_get_contents($file->getPathname())) as $token) {
                if (is_array($token) && in_array($token[0], [T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)) {
                    self::assertStringNotContainsString(
                        'laranail-console::',
                        $token[1],
                        "{$file->getPathname()}:{$token[2]} looks up a string through the deprecated laranail-console:: namespace.",
                    );
                }
            }
        }

        // 200+ files on 2026-10-05; a walk that stops matching must fail, not pass empty.
        self::assertGreaterThan(150, $files, 'too few source files scanned; the walk no longer matches.');
    }

    public function test_the_lang_files_publish_to_the_canonical_directory(): void
    {
        $paths = ServiceProvider::pathsToPublish(ConsoleServiceProvider::class, 'laranail::console-lang');

        self::assertCount(1, $paths);
        self::assertSame(
            str_replace('\\', '/', $this->app->langPath('vendor/laranail/console')),
            str_replace('\\', '/', (string) array_values($paths)[0]),
        );
    }
}
