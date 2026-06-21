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

    private const array PYTHON_KEYWORDS = [
        'def', 'class', 'if', 'elif', 'else', 'for', 'while', 'return', 'import',
        'from', 'as', 'with', 'try', 'except', 'finally', 'raise', 'lambda', 'pass',
        'break', 'continue', 'yield', 'global', 'nonlocal', 'async', 'await', 'and',
        'or', 'not', 'in', 'is', 'None', 'True', 'False', 'self', 'del', 'assert',
    ];

    private const array SQL_KEYWORDS = [
        'select', 'from', 'where', 'insert', 'into', 'values', 'update', 'set',
        'delete', 'create', 'alter', 'drop', 'table', 'index', 'view', 'join',
        'left', 'right', 'inner', 'outer', 'full', 'on', 'group', 'by', 'order',
        'having', 'limit', 'offset', 'distinct', 'as', 'and', 'or', 'not', 'null',
        'is', 'in', 'like', 'between', 'union', 'all', 'asc', 'desc', 'count',
        'sum', 'avg', 'min', 'max', 'primary', 'key', 'foreign', 'references',
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
        return in_array(
            $this->normalize($language),
            ['php', 'json', 'bash', 'yaml', 'js', 'python', 'sql', 'html', 'css', 'diff'],
            true,
        );
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
            'python' => $this->highlightPython($line),
            'sql' => $this->highlightSql($line),
            'html' => $this->highlightHtml($line),
            'css' => $this->highlightCss($line),
            'diff' => $this->highlightDiff($line),
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
            'py' => 'python',
            'xml', 'htm' => 'html',
            'patch' => 'diff',
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

    private function highlightPython(string $line): string
    {
        $keywords = implode('|', self::PYTHON_KEYWORDS);
        $pattern = '/(?P<comment>#.*$)'
            . '|(?P<string>"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\')'
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

    private function highlightSql(string $line): string
    {
        $keywords = implode('|', self::SQL_KEYWORDS);
        $pattern = '/(?P<comment>--.*$|\/\*.*?\*\/)'
            . '|(?P<string>\'(?:\'\'|[^\'])*\')'
            . '|(?P<num>\b\d+(?:\.\d+)?\b)'
            . '|(?P<kw>\b(?:' . $keywords . ')\b)/i';

        return (string) preg_replace_callback($pattern, fn (array $m): string => match (true) {
            ($m['comment'] ?? '') !== '' => $this->style('muted')->apply($m['comment']),
            ($m['string'] ?? '') !== '' => $this->style('success')->apply($m['string']),
            ($m['num'] ?? '') !== '' => $this->style('warning')->apply($m['num']),
            ($m['kw'] ?? '') !== '' => $this->style('primary')->apply($m['kw']),
            default => $m[0],
        }, $line);
    }

    private function highlightHtml(string $line): string
    {
        $pattern = '/(?P<comment><!--.*?-->)'
            . '|(?P<tag><\/?[a-zA-Z][\w-]*)'
            . '|(?P<string>"(?:[^"]*)"|\'(?:[^\']*)\')'
            . '|(?P<attr>[a-zA-Z-]+(?==))/';

        return (string) preg_replace_callback($pattern, fn (array $m): string => match (true) {
            ($m['comment'] ?? '') !== '' => $this->style('muted')->apply($m['comment']),
            ($m['tag'] ?? '') !== '' => $this->style('primary')->apply($m['tag']),
            ($m['string'] ?? '') !== '' => $this->style('success')->apply($m['string']),
            ($m['attr'] ?? '') !== '' => $this->style('accent')->apply($m['attr']),
            default => $m[0],
        }, $line);
    }

    private function highlightCss(string $line): string
    {
        $pattern = '/(?P<comment>\/\*.*?\*\/)'
            . '|(?P<atrule>@[\w-]+)'
            . '|(?P<string>"(?:[^"]*)"|\'(?:[^\']*)\')'
            . '|(?P<hex>#[0-9a-fA-F]{3,8}\b)'
            . '|(?P<prop>[a-zA-Z-]+(?=\s*:))'
            . '|(?P<num>\b\d+(?:\.\d+)?(?:px|em|rem|%|vh|vw|s|ms)?\b)/';

        return (string) preg_replace_callback($pattern, fn (array $m): string => match (true) {
            ($m['comment'] ?? '') !== '' => $this->style('muted')->apply($m['comment']),
            ($m['atrule'] ?? '') !== '' => $this->style('primary')->apply($m['atrule']),
            ($m['string'] ?? '') !== '' => $this->style('success')->apply($m['string']),
            ($m['hex'] ?? '') !== '' => $this->style('accent')->apply($m['hex']),
            ($m['prop'] ?? '') !== '' => $this->style('accent')->apply($m['prop']),
            ($m['num'] ?? '') !== '' => $this->style('warning')->apply($m['num']),
            default => $m[0],
        }, $line);
    }

    /**
     * Unified-diff highlighting — colour the whole line by its leading marker.
     */
    private function highlightDiff(string $line): string
    {
        if (str_starts_with($line, '+') && ! str_starts_with($line, '+++')) {
            return $this->style('success')->apply($line);
        }

        if (str_starts_with($line, '-') && ! str_starts_with($line, '---')) {
            return $this->style('danger')->apply($line);
        }

        if (str_starts_with($line, '@@') || str_starts_with($line, '+++') || str_starts_with($line, '---')) {
            return $this->style('info')->apply($line);
        }

        return $line;
    }

    private function style(string $role): Style
    {
        return Style::make($this->capabilities)->fg($this->theme->color($role) ?? '#64748b');
    }
}
