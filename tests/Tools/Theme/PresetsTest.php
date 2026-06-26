<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Theme;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Theme\Palette;
use Simtabi\Laranail\Console\Tools\Theme\Presets;
use Simtabi\Laranail\Console\Tools\Theme\Theme;

final class PresetsTest extends TestCase
{
    private const array ROLES = ['primary', 'accent', 'success', 'warning', 'danger', 'info', 'muted'];

    public function test_registry_lists_the_builtins(): void
    {
        foreach (['dracula', 'nord', 'solarized', 'monochrome', 'github'] as $name) {
            self::assertTrue(Presets::has($name), "should have {$name}");
            self::assertContains($name, Presets::names());
        }
        self::assertFalse(Presets::has('nope'));
        self::assertNull(Presets::get('nope'));
    }

    /**
     * @return list<array{0:string}>
     */
    public static function presetNames(): array
    {
        return [['dracula'], ['nord'], ['solarized'], ['monochrome'], ['github']];
    }

    #[DataProvider('presetNames')]
    public function test_each_preset_defines_all_seven_roles_with_valid_hex(string $name): void
    {
        $palette = Theme::preset($name)->palette();

        foreach (self::ROLES as $role) {
            $hex = $palette->get($role);
            self::assertNotNull($hex, "{$name}.{$role} missing");
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $hex, "{$name}.{$role} = {$hex}");
        }
    }

    public function test_preset_differs_from_default(): void
    {
        self::assertNotSame(Palette::make()->get('primary'), Theme::preset('nord')->color('primary'));
        self::assertSame('#bd93f9', Theme::preset('dracula')->color('primary'));
    }

    public function test_unknown_preset_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Theme::preset('does-not-exist');
    }
}
