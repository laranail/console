<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Document;

use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Theme\Theme;
use Simtabi\Laranail\Console\Tools\Typography\CodeBlock;
use Simtabi\Laranail\Console\Tools\Typography\Paragraph;
use Simtabi\Laranail\Console\Tools\Widgets\Rule;
use Stringable;

/**
 * Render a documented Markdown subset to the terminal via the {@see Document}
 * composer + the design-system theme. Supported (block-level): ATX headings
 * (#–######), paragraphs, unordered/ordered/task lists, blockquotes, fenced code
 * blocks, horizontal rules. Inline emphasis (`**bold**`, `*italic*`, `` `code` ``)
 * and links `[label](url)` are normalised to plain text (inline ANSI styling is a
 * future addition); `:emoji:` shortcodes are resolved by the paragraph layer.
 */
final readonly class Markdown implements Stringable
{
    private Capabilities $capabilities;

    private Theme $theme;

    public function __construct(private string $markdown, ?Capabilities $capabilities = null, ?Theme $theme = null)
    {
        $this->capabilities = $capabilities ?? Capabilities::detect();
        $this->theme = $theme ?? Theme::resolve();
    }

    public static function make(string $markdown): self
    {
        return new self($markdown);
    }

    public function toDocument(): Document
    {
        $doc = new Document($this->capabilities, $this->theme);
        $lines = preg_split('/\r\n|\r|\n/', $this->markdown) ?: [];
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            // Fenced code block.
            if (preg_match('/^```+\s*([\w.-]*)\s*$/', $trimmed, $m) === 1) {
                $code = [];
                $caption = $m[1];
                $i++;
                while ($i < $count && preg_match('/^```+\s*$/', trim($lines[$i])) !== 1) {
                    $code[] = $lines[$i];
                    $i++;
                }
                $block = new CodeBlock(implode("\n", $code), $this->capabilities, $this->theme);
                $doc->add($caption !== '' ? $block->caption($caption) : $block);

                continue;
            }

            // Horizontal rule.
            if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trimmed) === 1) {
                $doc->add(Rule::make());

                continue;
            }

            // Heading.
            if (preg_match('/^(#{1,6})\s+(.*)$/', $trimmed, $m) === 1) {
                $doc->heading($this->inline($m[2]), strlen($m[1]));

                continue;
            }

            // Blockquote (consecutive `>` lines).
            if (str_starts_with($trimmed, '>')) {
                $quote = [];
                while ($i < $count && str_starts_with(trim($lines[$i]), '>')) {
                    $quote[] = ltrim(ltrim(trim($lines[$i]), '>'));
                    $i++;
                }
                $i--;
                $doc->blockQuote($this->inline(implode(' ', $quote)));

                continue;
            }

            // Task list (consecutive `- [ ]` / `- [x]`).
            if (preg_match('/^[-*]\s+\[[ xX]\]\s+/', $trimmed) === 1) {
                $tasks = [];
                while ($i < $count && preg_match('/^[-*]\s+\[([ xX])\]\s+(.*)$/', trim($lines[$i]), $m) === 1) {
                    $tasks[$this->inline($m[2])] = strtolower($m[1]) === 'x';
                    $i++;
                }
                $i--;
                $doc->taskList($tasks);

                continue;
            }

            // Ordered list.
            if (preg_match('/^\d+\.\s+/', $trimmed) === 1) {
                $items = [];
                while ($i < $count && preg_match('/^\d+\.\s+(.*)$/', trim($lines[$i]), $m) === 1) {
                    $items[] = $this->inline($m[1]);
                    $i++;
                }
                $i--;
                $doc->orderedList($items);

                continue;
            }

            // Unordered list.
            if (preg_match('/^[-*]\s+/', $trimmed) === 1) {
                $items = [];
                while ($i < $count && preg_match('/^[-*]\s+(.*)$/', trim($lines[$i]), $m) === 1
                    && preg_match('/^[-*]\s+\[[ xX]\]/', trim($lines[$i])) !== 1) {
                    $items[] = $this->inline($m[1]);
                    $i++;
                }
                $i--;
                $doc->bulletList($items);

                continue;
            }

            // Paragraph (consecutive non-blank, non-special lines).
            $para = [];
            while ($i < $count && trim($lines[$i]) !== '' && ! $this->isBlockStart(trim($lines[$i]))) {
                $para[] = trim($lines[$i]);
                $i++;
            }
            $i--;
            // Paragraphs get full inline styling (bold/italic/code/link); headings,
            // lists and quotes use the plain normalisation above.
            $styled = InlineMarkup::make($this->capabilities, $this->theme)->render(implode(' ', $para));
            $doc->add(Paragraph::rich($styled, $this->capabilities, $this->theme));
        }

        return $doc;
    }

    public function render(): string
    {
        return $this->toDocument()->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }

    private function isBlockStart(string $line): bool
    {
        return preg_match('/^(#{1,6}\s|>|[-*]\s|\d+\.\s|```|-{3,}$|\*{3,}$|_{3,}$)/', $line) === 1;
    }

    /**
     * Normalise inline markdown to plain text (v1: emphasis stripped, links → "label (url)").
     */
    private function inline(string $text): string
    {
        // Links [label](url) → "label (url)".
        $text = (string) preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '$1 ($2)', $text);
        // Bold/italic/inline-code markers stripped (text kept).
        $text = (string) preg_replace('/\*\*([^*]+)\*\*|__([^_]+)__/', '$1$2', $text);
        $text = (string) preg_replace('/\*([^*]+)\*|_([^_]+)_/', '$1$2', $text);

        return (string) preg_replace('/`([^`]+)`/', '$1', $text);
    }
}
