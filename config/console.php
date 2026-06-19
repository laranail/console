<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Output rendering
    |--------------------------------------------------------------------------
    |
    | "auto" lets the package detect terminal capabilities at runtime. Force a
    | value to override detection (useful in CI or constrained terminals).
    |
    | unicode : auto | true | false   — use Unicode glyphs or ASCII fallbacks
    | colors  : auto | always | never — emit ANSI colour
    | symbols : auto | fancy | ascii  — status/tree/box glyph set
    | width   : null (autodetect) or an integer column count fallback
    |
    */
    'output' => [
        'unicode' => env('CONSOLE_UNICODE', 'auto'),
        'colors' => env('CONSOLE_COLORS', 'auto'),
        'symbols' => env('CONSOLE_SYMBOLS', 'auto'),
        'width' => env('CONSOLE_WIDTH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Spinner defaults
    |--------------------------------------------------------------------------
    | frames: braille | dots | line | breath
    */
    'spinner' => [
        'frames' => env('CONSOLE_SPINNER_FRAMES', 'braille'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Progress bar defaults
    |--------------------------------------------------------------------------
    | format: minimal | classic | detailed
    | glyphs: blocks | ascii | dots | arrows | gradient
    */
    'progress' => [
        'format' => env('CONSOLE_PROGRESS_FORMAT', 'detailed'),
        'glyphs' => env('CONSOLE_PROGRESS_GLYPHS', 'blocks'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hyperlinks
    |--------------------------------------------------------------------------
    | Only these URL schemes are allowed in OSC-8 terminal hyperlinks. Anything
    | else is rendered as plain "label (url)" text. Prevents link spoofing.
    */
    'links' => [
        'allowed_schemes' => ['http', 'https', 'mailto'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error logging
    |--------------------------------------------------------------------------
    | redact_keys          : context keys scrubbed before an exception is logged
    | trace_in_debug_only  : only log stack traces when app.debug is true
    | channel              : log channel name, or null for the default
    */
    'logging' => [
        'redact_keys' => ['password', 'secret', 'token', 'key', 'authorization'],
        'trace_in_debug_only' => true,
        'channel' => env('CONSOLE_LOG_CHANNEL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Interaction
    |--------------------------------------------------------------------------
    | When true, asking for a required value (e.g. a password) in a
    | non-interactive session throws instead of silently returning empty.
    */
    'interaction' => [
        'non_interactive_required_throws' => (bool) env('CONSOLE_REQUIRE_INTERACTIVE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    | Translation locale for console strings. null follows the application locale.
    */
    'locale' => env('CONSOLE_LOCALE'),

];
