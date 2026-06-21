<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Typography;

use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_supports_known_languages_and_aliases(): void
    {
        $h = SyntaxHighlighter::make();
        $langs = [
            'php', 'JSON', 'bash', 'sh', 'shell', 'yaml', 'yml', 'js', 'javascript', 'node',
            'python', 'py', 'sql', 'html', 'xml', 'htm', 'css', 'diff', 'patch',
        ];
        foreach ($langs as $lang) {
            self::assertTrue($h->supports($lang), "should support {$lang}");
        }
        self::assertFalse($h->supports('rust'));
    }

    /**
     * @return list<array{0:string, 1:string, 2:string}>
     */
    public static function languageCases(): array
    {
        return [
            'bash' => ['bash', 'if [ -n "$x" ]; then echo hi; fi # note', 'echo'],
            'bash alias sh' => ['sh', 'export FOO=1', 'export'],
            'yaml' => ['yaml', 'name: value # comment', 'name'],
            'js' => ['js', 'const x = `tpl`; // c', 'const'],
            'js alias' => ['javascript', 'function f() { return 1 }', 'function'],
            'python' => ['python', 'def go(self):  # run', 'def'],
            'python alias py' => ['py', 'import os', 'import'],
            'sql' => ['sql', 'SELECT * FROM users WHERE id = 1 -- c', 'SELECT'],
            'html' => ['html', '<a href="/x">link</a>', 'href'],
            'html alias xml' => ['xml', '<root attr="v"/>', 'attr'],
            'css' => ['css', '.btn { color: #fff; margin: 4px } /* c */', 'color'],
            'diff' => ['diff', '+added line', 'added'],
            'diff alias patch' => ['patch', '-removed line', 'removed'],
        ];
    }

    #[DataProvider('languageCases')]
    public function test_new_languages_are_coloured(string $lang, string $code, string $needle): void
    {
        Capabilities::fake(colors: true, unicode: true, interactive: false);

        $out = SyntaxHighlighter::make()->highlightLine($code, $lang);

        self::assertStringContainsString("\033[", $out, "{$lang} should emit ANSI");
        self::assertStringContainsString($needle, $out);
    }

    public function test_php_tokens_are_coloured(): void
    {
        Capabilities::fake(colors: true, unicode: true, interactive: false);

        $out = SyntaxHighlighter::make()->highlightLine('return $x; // done', 'php');

        self::assertStringContainsString("\033[", $out);   // coloured
        self::assertStringContainsString('return', $out);
        self::assertStringContainsString('$x', $out);
    }

    public function test_very_long_line_is_returned_unhighlighted(): void
    {
        Capabilities::fake(colors: true, unicode: true, interactive: false);

        // a pathological unterminated html comment, well over the 4000-char guard
        $line = '<!--' . str_repeat('a', 8000);

        $start = microtime(true);
        $out = SyntaxHighlighter::make()->highlightLine($line, 'html');
        $elapsed = microtime(true) - $start;

        self::assertSame($line, $out);              // returned unchanged (skipped)
        self::assertLessThan(0.5, $elapsed);        // no quadratic backtracking
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
