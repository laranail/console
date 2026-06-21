<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Theme;

use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Simtabi\Laranail\Console\Tools\Theme\Theme;

/**
 * Theme::resolve() reads config, so it runs under Testbench (config available).
 */
final class ThemeResolveTest extends TestCase
{
    public function test_resolve_uses_the_configured_preset(): void
    {
        config()->set('console.theme.preset', 'dracula');
        config()->set('console.theme.palette', []);

        self::assertSame('#bd93f9', Theme::resolve()->color('primary'));
    }

    public function test_palette_overrides_win_over_the_preset(): void
    {
        config()->set('console.theme.preset', 'dracula');
        config()->set('console.theme.palette', ['primary' => '#123456']);

        $theme = Theme::resolve();
        self::assertSame('#123456', $theme->color('primary'));       // override wins
        self::assertSame('#8be9fd', $theme->color('accent'));        // preset fills the rest
    }

    public function test_unknown_preset_falls_back_to_defaults(): void
    {
        config()->set('console.theme.preset', 'bogus');
        config()->set('console.theme.palette', []);

        // lenient: resolve() never throws on a bad config preset
        self::assertSame('#7c3aed', Theme::resolve()->color('primary'));
    }
}
