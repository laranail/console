<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Document;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Document\Markdown;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;

final class MarkdownTableTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_renders_a_gfm_pipe_table(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 80, interactive: false);

        $md = <<<'MD'
            | Name | Role |
            | --- | --- |
            | Ada | Engineer |
            | Bob | Designer |
            MD;

        $out = Markdown::make($md)->render();

        foreach (['Name', 'Role', 'Ada', 'Engineer', 'Bob', 'Designer'] as $cell) {
            self::assertStringContainsString($cell, $out);
        }
        self::assertStringContainsString('│', $out); // table border drawn
    }

    public function test_table_without_leading_trailing_pipes(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 80, interactive: false);

        $out = Markdown::make("a | b\n--- | ---\n1 | 2")->render();

        self::assertStringContainsString('a', $out);
        self::assertStringContainsString('2', $out);
    }

    public function test_ragged_rows_are_normalised_to_header_columns(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 80, interactive: false);

        // second data row has an extra cell; must not error
        $out = Markdown::make("| A | B |\n|---|---|\n| 1 | 2 |\n| 3 | 4 | 5 |")->render();
        self::assertStringContainsString('3', $out);
    }

    public function test_table_is_responsive(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 28, interactive: false);

        $md = "| Name | Description |\n|---|---|\n| x | " . str_repeat('word ', 20) . '|';
        foreach (explode("\n", rtrim(Markdown::make($md)->render(), "\n")) as $line) {
            self::assertLessThanOrEqual(28, DisplayWidth::of($line));
        }
    }

    public function test_pipe_text_without_separator_is_not_a_table(): void
    {
        Capabilities::fake(colors: false, unicode: true, width: 80, interactive: false);

        // a paragraph that merely contains a pipe must not become a table
        $out = Markdown::make("use a | b pipe in prose\nand more prose")->render();
        self::assertStringNotContainsString('│', $out);
    }
}
