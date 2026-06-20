<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Typography;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Typography\CodeBlock;
use Simtabi\Laranail\Console\Tools\Typography\SyntaxHighlighter;

final class SyntaxHighlightTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_supports_php_and_json_only(): void
    {
        $h = SyntaxHighlighter::make();
        self::assertTrue($h->supports('php'));
        self::assertTrue($h->supports('JSON'));
        self::assertFalse($h->supports('rust'));
    }

    public function test_php_tokens_are_coloured(): void
    {
        Capabilities::fake(colors: true, unicode: true, interactive: false);

        $out = SyntaxHighlighter::make()->highlightLine('return $x; // done', 'php');

        self::assertStringContainsString("\033[", $out);   // coloured
        self::assertStringContainsString('return', $out);
        self::assertStringContainsString('$x', $out);
    }

    public function test_unknown_language_is_plain(): void
    {
        Capabilities::fake(colors: true, unicode: true, interactive: false);

        self::assertSame('let x = 1;', SyntaxHighlighter::make()->highlightLine('let x = 1;', 'rust'));
    }

    public function test_codeblock_highlights_when_language_set(): void
    {
        Capabilities::fake(colors: true, unicode: true, width: 80, interactive: false);

        $plain = CodeBlock::make('return 1;')->render();
        $highlighted = CodeBlock::make('return 1;')->language('php')->render();

        self::assertNotSame($plain, $highlighted);
        self::assertStringContainsString('return', $highlighted);
    }
}
