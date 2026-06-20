<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Typography;

use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Style;
use Simtabi\Laranail\Console\Tools\Theme\Theme;

/**
 * A deliberately small, regex-based syntax highlighter for fenced code blocks.
 * Supports `php`, `json`, `bash`, `yaml` and `js` (plus aliases: `sh`/`shell`/`zsh`,
 * `yml`, `javascript`/`node`/`mjs`) — comments, strings, numbers, keywords/keys/
 * variables; any other language renders plain. Per-line (no cross-line string or
 * comment state) — good enough for docs, not a full lexer. Colours come from the theme.
 */
final readonly class SyntaxHighlighter
{
    private const array PHP_KEYWORDS = [
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class',
        'clone', 'const', 'continue', 'declare', 'default', 'do', 'echo', 'else', 'elseif',
        'enum', 'extends', 'final', 'finally', 'fn', 'for', 'foreach', 'function', 'global',
        'if', 'implements', 'instanceof', 'interface', 'match', 'namespace', 'new', 'or',
        'private', 'protected', 'public', 'readonly', 'return', 'static', 'switch', 'throw',
        'trait', 'try', 'use', 'var', 'while', 'yield', 'true', 'false', 'null',
    ];

    private const array BASH_KEYWORDS = [
        'if', 'then', 'else', 'elif', 'fi', 'for', 'while', 'until', 'do', 'done',
        'case', 'esac', 'in', 'select', 'function', 'return', 'break', 'continue',
        'export', 'local', 'readonly', 'declare', 'echo', 'printf', 'cd', 'exit',
        'set', 'unset', 'source', 'eval', 'exec', 'trap', 'shift',
    ];

    private const array JS_KEYWORDS = [
        'const', 'let', 'var', 'function', 'return', 'if', 'else', 'for', 'while',
        'do', 'switch', 'case', 'break', 'continue', 'class', 'extends', 'new',
        'this', 'super', 'import', 'export', 'from', 'default', 'async', 'await',
        'yield', 'typeof', 'instanceof', 'in', 'of', 'delete', 'void', 'try',
        'catch', 'finally', 'throw', 'null', 'true', 'false', 'undefined',
    ];

    private Capabilities $capabilities;

    private Theme $theme;

    public function __construct(?Capabilities $capabilities = null, ?Theme $theme = null)
    {
        $this->capabilities = $capabilities ?? Capabilities::detect();
        $this->theme = $theme ?? Theme::resolve();
    }

    public static function make(?Capabilities $capabilities = null, ?Theme $theme = null): self
    {
        return new self($capabilities, $theme);
    }

    public function supports(string $language): bool
    {
        return in_array($this->normalize($language), ['php', 'json', 'bash', 'yaml', 'js'], true);
    }

    /**
     * Highlight one line. Assumes plain input (no existing ANSI); returns a styled
     * string. Unknown languages return the line unchanged.
     */
    public function highlightLine(string $line, string $language): string
    {
        return match ($this->normalize($language)) {
            'php' => $this->highlightPhp($line),
            'json' => $this->highlightJson($line),
            'bash' => $this->highlightBash($line),
            'yaml' => $this->highlightYaml($line),
            'js' => $this->highlightJs($line),
            default => $line,
        };
    }

    /**
     * Normalise common language aliases to a canonical key.
     */
    private function normalize(string $language): string
    {
        return match (strtolower(trim($language))) {
            'sh', 'shell', 'zsh' => 'bash',
            'yml' => 'yaml',
            'javascript', 'node', 'mjs' => 'js',
            default => strtolower(trim($language)),
        };
    }

    private function highlightPhp(string $line): string
    {
        $keywords = implode('|', self::PHP_KEYWORDS);
        $pattern = '/(?P<comment>\/\/.*$|#.*$|\/\*.*?\*\/)'
            . '|(?P<string>"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\')'
            . '|(?P<var>\$\w+)'
            . '|(?P<num>\b\d+(?:\.\d+)?\b)'
            . '|(?P<kw>\b(?:' . $keywords . ')\b)/';

        return (string) preg_replace_callback($pattern, fn (array $m): string => match (true) {
            ($m['comment'] ?? '') !== '' => $this->style('muted')->apply($m['comment']),
            ($m['string'] ?? '') !== '' => $this->style('success')->apply($m['string']),
            ($m['var'] ?? '') !== '' => $this->style('info')->apply($m['var']),
            ($m['num'] ?? '') !== '' => $this->style('warning')->apply($m['num']),
            ($m['kw'] ?? '') !== '' => $this->style('primary')->apply($m['kw']),
            default => $m[0],
        }, $line);
    }

    private function highlightJson(string $line): string
    {
        $pattern = '/(?P<key>"(?:\\\\.|[^"\\\\])*"(?=\s*:))'
            . '|(?P<string>"(?:\\\\.|[^"\\\\])*")'
            . '|(?P<bool>\b(?:true|false|null)\b)'
            . '|(?P<num>-?\b\d+(?:\.\d+)?\b)/';

        return (string) preg_replace_callback($pattern, fn (array $m): string => match (true) {
            ($m['key'] ?? '') !== '' => $this->style('accent')->apply($m['key']),
            ($m['string'] ?? '') !== '' => $this->style('success')->apply($m['string']),
            ($m['bool'] ?? '') !== '' => $this->style('primary')->apply($m['bool']),
            ($m['num'] ?? '') !== '' => $this->style('warning')->apply($m['num']),
            default => $m[0],
        }, $line);
    }

    private function highlightBash(string $line): string
    {
        $keywords = implode('|', self::BASH_KEYWORDS);
        $pattern = '/(?P<comment>(?<![\w$])#.*$)'
            . '|(?P<string>"(?:\\\\.|[^"\\\\])*"|\'[^\']*\')'
            . '|(?P<var>\$\{?\w+\}?)'
            . '|(?P<num>\b\d+\b)'
            . '|(?P<kw>\b(?:' . $keywords . ')\b)/';

        return (string) preg_replace_callback($pattern, fn (array $m): string => match (true) {
            ($m['comment'] ?? '') !== '' => $this->style('muted')->apply($m['comment']),
            ($m['string'] ?? '') !== '' => $this->style('success')->apply($m['string']),
            ($m['var'] ?? '') !== '' => $this->style('info')->apply($m['var']),
            ($m['num'] ?? '') !== '' => $this->style('warning')->apply($m['num']),
            ($m['kw'] ?? '') !== '' => $this->style('primary')->apply($m['kw']),
            default => $m[0],
        }, $line);
    }

    private function highlightYaml(string $line): string
    {
        $pattern = '/(?P<comment>(?<!\S)#.*$)'
            . '|(?P<key>^\s*[\w.-]+(?=\s*:))'
            . '|(?P<string>"(?:\\\\.|[^"\\\\])*"|\'[^\']*\')'
            . '|(?P<bool>\b(?:true|false|null|yes|no|on|off)\b)'
            . '|(?P<num>-?\b\d+(?:\.\d+)?\b)/';

        return (string) preg_replace_callback($pattern, fn (array $m): string => match (true) {
            ($m['comment'] ?? '') !== '' => $this->style('muted')->apply($m['comment']),
            ($m['key'] ?? '') !== '' => $this->style('accent')->apply($m['key']),
            ($m['string'] ?? '') !== '' => $this->style('success')->apply($m['string']),
            ($m['bool'] ?? '') !== '' => $this->style('primary')->apply($m['bool']),
            ($m['num'] ?? '') !== '' => $this->style('warning')->apply($m['num']),
            default => $m[0],
        }, $line);
    }

    private function highlightJs(string $line): string
    {
        $keywords = implode('|', self::JS_KEYWORDS);
        $pattern = '/(?P<comment>\/\/.*$|\/\*.*?\*\/)'
            . '|(?P<string>"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'|`(?:\\\\.|[^`\\\\])*`)'
            . '|(?P<num>\b\d+(?:\.\d+)?\b)'
            . '|(?P<kw>\b(?:' . $keywords . ')\b)/';

        return (string) preg_replace_callback($pattern, fn (array $m): string => match (true) {
            ($m['comment'] ?? '') !== '' => $this->style('muted')->apply($m['comment']),
            ($m['string'] ?? '') !== '' => $this->style('success')->apply($m['string']),
            ($m['num'] ?? '') !== '' => $this->style('warning')->apply($m['num']),
            ($m['kw'] ?? '') !== '' => $this->style('primary')->apply($m['kw']),
            default => $m[0],
        }, $line);
    }

    private function style(string $role): Style
    {
        return Style::make($this->capabilities)->fg($this->theme->color($role) ?? '#64748b');
    }
}
