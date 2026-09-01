<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Widgets\KeyValue;

final class KeyValueTest extends TestCase
{
    public function test_renders_aligned_pairs(): void
    {
        $out = KeyValue::make(['name' => 'ada', 'occupation' => 'engineer'])->render();
        $lines = explode("\n", $out);

        self::assertCount(2, $lines);
        self::assertStringContainsString('ada', $lines[0]);
        self::assertStringContainsString('engineer', $lines[1]);
        // Keys are padded to the widest key, so the separators line up.
        $sepCols = array_map(static fn (string $l): int => DisplayWidth::of(substr($l, 0, (int) strpos($l, ':'))), $lines);
        self::assertCount(1, array_unique($sepCols));
    }

    public function test_add_and_separator_and_empty(): void
    {
        self::assertSame('', KeyValue::make()->render());

        $out = KeyValue::make()->add('k', 'v')->add('count', 0)->separator('=')->render();
        self::assertStringContainsString('k     = v', $out);
        self::assertStringContainsString('count = 0', $out);
    }

    public function test_sanitizes_control_characters(): void
    {
        $out = KeyValue::make(["ke\x07y" => "va\033lue"])->render();
        self::assertStringNotContainsString("\x07", $out);
        self::assertStringNotContainsString("\033", $out);
    }
}
