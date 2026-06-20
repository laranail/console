<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Document;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Document\Markdown;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;

final class RichListsQuotesTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_list_items_render_inline_styling_when_coloured(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 80, interactive: false);

        $out = Markdown::make("- a **bold** word\n- some `code` here")->render();

        self::assertStringContainsString("\033[", $out);     // styled
        self::assertStringContainsString('bold', $out);
        self::assertStringContainsString('code', $out);
        self::assertStringNotContainsString('**', $out);     // markers consumed
    }

    public function test_blockquote_renders_inline_styling(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 80, interactive: false);

        $out = Markdown::make('> see the **important** note')->render();

        self::assertStringContainsString("\033[", $out);
        self::assertStringContainsString('important', $out);
        self::assertStringNotContainsString('**', $out);
    }

    public function test_task_list_renders_inline_styling(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 80, interactive: false);

        $out = Markdown::make("- [x] ship `v0.7`\n- [ ] write **docs**")->render();

        self::assertStringContainsString('v0.7', $out);
        self::assertStringContainsString('docs', $out);
        self::assertStringNotContainsString('**', $out);
    }

    public function test_lists_degrade_to_plain_without_colour(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 80, interactive: false);

        $out = Markdown::make('- a **bold** word with `code`')->render();

        self::assertStringContainsString('bold', $out);
        self::assertStringContainsString('code', $out);
        self::assertStringNotContainsString('**', $out);
        self::assertStringNotContainsString("\033[", $out); // no ANSI
    }
}
