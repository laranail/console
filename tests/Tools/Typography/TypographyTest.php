<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Typography;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Simtabi\Laranail\Console\Tools\Typography\Code;
use Simtabi\Laranail\Console\Tools\Typography\CodeBlock;
use Simtabi\Laranail\Console\Tools\Typography\Heading;
use Simtabi\Laranail\Console\Tools\Typography\Link;
use Simtabi\Laranail\Console\Tools\Typography\ListBlock;
use Simtabi\Laranail\Console\Tools\Typography\Paragraph;
use Simtabi\Laranail\Console\Tools\Typography\Quote;
use Simtabi\Laranail\Console\Tools\Typography\Text;

final class TypographyTest extends TestCase
{
    protected function setUp(): void
    {
        // Plain (no colour) + unicode + non-TTY → deterministic, assertable output.
        Capabilities::fake(colors: false, unicode: true, width: 40, interactive: false);
    }

    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_heading_underlines_h1(): void
    {
        $lines = Heading::make('Hi', 1)->renderLines();

        self::assertSame('Hi', $lines[0]);
        self::assertSame('══', $lines[1]); // underline matches text width
    }

    public function test_heading_level_is_clamped(): void
    {
        self::assertCount(1, Heading::make('x', 9)->renderLines()); // h6, no underline
    }

    public function test_paragraph_wraps_to_width(): void
    {
        $out = Paragraph::make(str_repeat('word ', 20))->width(20)->renderLines();

        foreach ($out as $line) {
            self::assertLessThanOrEqual(20, DisplayWidth::of($line));
        }
        self::assertGreaterThan(1, count($out));
    }

    public function test_paragraph_center_and_justify(): void
    {
        $centered = Paragraph::make('hi')->width(10)->align('center')->renderLines();
        self::assertSame('hi', trim($centered[0]));
        self::assertStringStartsWith('    ', $centered[0]); // centred → leading pad

        $justified = Paragraph::make('a b c d e f g h')->width(20)->align('justify')->renderLines();
        self::assertNotEmpty($justified);
    }

    public function test_link_falls_back_to_plain_when_not_interactive(): void
    {
        self::assertSame('docs (https://x.com)', Link::make('docs', 'https://x.com')->render());
    }

    public function test_quote_and_code(): void
    {
        self::assertSame('“hi”', Quote::make('hi')->render());
        self::assertSame(' x ', Code::make('x')->render());
    }

    public function test_code_block_has_gutter(): void
    {
        $out = CodeBlock::make("a\nb")->caption('php')->renderLines();

        self::assertSame('php', $out[0]);
        self::assertStringContainsString('a', $out[1]);
        self::assertStringStartsWith('▏ ', $out[1]);
    }

    public function test_list_unordered_ordered_task_definition(): void
    {
        self::assertSame(['• one', '• two'], ListBlock::make(['one', 'two'])->renderLines());
        self::assertSame(['1. one', '2. two'], ListBlock::make(['one', 'two'])->ordered()->renderLines());

        $tasks = ListBlock::make()->tasks(['done' => true, 'todo' => false])->renderLines();
        self::assertSame('☑ done', $tasks[0]);
        self::assertSame('☐ todo', $tasks[1]);

        $def = ListBlock::make()->definition(['Term' => 'meaning'])->renderLines();
        self::assertSame('Term', $def[0]);
        self::assertSame('  meaning', $def[1]);
    }

    public function test_text_builder_appends_and_resolves_emoji(): void
    {
        // ascii mode (unicode true here, so emoji is unicode) — use a known shortcode
        $out = Text::make('Go ')->text(':rocket:')->render();
        self::assertStringContainsString('🚀', $out);
    }

    public function test_text_no_colour_is_plain(): void
    {
        self::assertSame('hello', Text::make('hello')->fg('red')->bold()->render());
    }
}
