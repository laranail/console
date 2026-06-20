<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Document;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Document\Document;
use Simtabi\Laranail\Console\Tools\Document\Markdown;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;

final class DocumentTest extends TestCase
{
    protected function setUp(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 40, interactive: false);
    }

    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_document_composes_blocks_with_spacing(): void
    {
        $out = Document::make()
            ->h1('Title')
            ->paragraph('Body text here.')
            ->bulletList(['one', 'two'])
            ->render();

        self::assertStringContainsString('Title', $out);
        self::assertStringContainsString('══', $out);
        self::assertStringContainsString('• one', $out);
        self::assertStringContainsString('Body text here.', $out);
        // a blank line between blocks
        self::assertStringContainsString("\n\n", $out);
    }

    public function test_document_width_clamps_children(): void
    {
        $out = Document::make()->width(20)->paragraph(str_repeat('word ', 20))->renderLines();

        foreach ($out as $line) {
            self::assertLessThanOrEqual(20, DisplayWidth::of($line));
        }
    }

    public function test_markdown_renders_block_elements(): void
    {
        $md = <<<'MD'
            # Heading

            A paragraph with **bold** and a [link](https://x.com).

            - one
            - two

            1. first
            2. second

            - [x] done
            - [ ] todo

            > quoted line

            ```php
            echo 1;
            ```
            MD;

        $out = Markdown::make($md)->render();

        self::assertStringContainsString('Heading', $out);
        self::assertStringContainsString('bold', $out); // markers stripped, text kept
        self::assertStringContainsString('https://x.com', $out); // link → "label (url)"
        self::assertStringContainsString('• one', $out);
        self::assertStringContainsString('1. first', $out);
        self::assertStringContainsString('☑ done', $out);
        self::assertStringContainsString('☐ todo', $out);
        self::assertStringContainsString('│ quoted line', $out);
        self::assertStringContainsString('echo 1;', $out);
    }
}
