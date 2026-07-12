<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Simtabi\Laranail\Console\Tools\Widgets\Header;
use Simtabi\Laranail\Console\Tools\Widgets\Summary;

/**
 * Widget strings resolve through the console:: translation namespace honouring
 * config('console.locale') — without mutating the host app's global locale.
 */
final class WidgetI18nTest extends TestCase
{
    public function test_widget_strings_follow_the_configured_locale(): void
    {
        app('translator')->addLines([
            'console.widgets.header.items' => 'objets',
            'console.widgets.summary.title' => 'RÉSUMÉ',
        ], 'fr', 'console');

        config(['console.locale' => 'fr']);

        self::assertStringContainsString('3 objets', Header::make('Files')->count(3)->render());
        self::assertStringContainsString('RÉSUMÉ', Summary::make(['total' => 1, 'success' => 1])->render());

        // The host app's global locale is untouched.
        self::assertSame('en', app()->getLocale());
    }

    public function test_default_locale_keeps_english(): void
    {
        config(['console.locale' => null]);

        self::assertStringContainsString('2 items', Header::make('X')->count(2)->render());
        self::assertStringContainsString('EXECUTION SUMMARY', Summary::make(['total' => 0])->render());
    }
}
