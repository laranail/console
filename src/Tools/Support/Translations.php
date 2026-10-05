<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Support;

use Illuminate\Support\Arr;

/**
 * The package's translation namespaces, and the one place its own strings are looked up.
 *
 * `laranail/console` is the canonical namespace: the composer package name, so a key names the
 * package that ships it, and published overrides live in `lang/vendor/laranail/console/`.
 * `laranail-console` is the namespace the package registered before 0.1.5; it is still registered
 * over the same files, so a host calling `__('laranail-console::…')` keeps working.
 *
 * Moving the internal lookups to the canonical namespace would, on its own, silently drop every
 * override a host made against the old one -- a file published to `lang/vendor/laranail-console/`
 * or lines added at runtime with `addLines(…, 'laranail-console')`. Both namespaces start from the
 * same packaged files, so they can only disagree when one of them was overridden. When they do,
 * the canonical one wins unless it is still the packaged default, in which case the old
 * namespace's override is what the host asked for.
 *
 * @internal Lookup helper for this package's own strings; not part of the public API.
 */
final class Translations
{
    /** The canonical translation namespace: `__('laranail/console::console.invalid_input')`. */
    public const string NAMESPACE = 'laranail/console';

    /**
     * @deprecated since 0.1.5, removal no earlier than the next minor after 0.1. Use
     *   {@see self::NAMESPACE} (`laranail/console::`). Still registered over the same files.
     */
    public const string LEGACY_NAMESPACE = 'laranail-console';

    private const string LANG_PATH = __DIR__ . '/../../../resources/lang';

    /**
     * The canonical, fully-qualified key: `laranail/console::<group>.<item>`.
     */
    public static function key(string $key): string
    {
        return self::NAMESPACE . '::' . $key;
    }

    /**
     * Translate `<group>.<item>` from this package's strings. Returns the canonical key itself when
     * no translator is bound or the key is missing, matching what `trans()` does for a miss.
     *
     * @param array<string, scalar|null> $replace
     */
    public static function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $canonicalKey = self::key($key);

        if (! function_exists('app') || ! app()->bound('translator')) {
            return $canonicalKey;
        }

        $canonical = trans($canonicalKey, [], $locale);
        $legacy = trans(self::LEGACY_NAMESPACE . '::' . $key, [], $locale);

        $useLegacy = $canonical !== $legacy
            && is_string($legacy)
            && $legacy !== self::LEGACY_NAMESPACE . '::' . $key
            && ($canonical === $canonicalKey || $canonical === self::packaged($key, $locale));

        $message = trans($useLegacy ? self::LEGACY_NAMESPACE . '::' . $key : $canonicalKey, $replace, $locale);

        return is_string($message) ? $message : $canonicalKey;
    }

    /**
     * The line as shipped in `resources/lang`, before any override, in the locale the translator
     * would use (requested, else current, else fallback).
     */
    private static function packaged(string $key, ?string $locale): mixed
    {
        [$group, $item] = array_pad(explode('.', $key, 2), 2, null);

        $translator = app('translator');
        $locales = array_unique(array_filter([
            $locale,
            method_exists($translator, 'getLocale') ? $translator->getLocale() : null,
            method_exists($translator, 'getFallback') ? $translator->getFallback() : null,
        ], is_string(...)));

        foreach ($locales as $candidate) {
            $file = self::LANG_PATH . '/' . $candidate . '/' . $group . '.php';

            if (! is_file($file)) {
                continue;
            }

            $lines = require $file;

            if (! is_array($lines)) {
                continue;
            }

            $line = $item === null ? $lines : Arr::get($lines, $item);

            if ($line !== null) {
                return $line;
            }
        }

        return null;
    }
}
