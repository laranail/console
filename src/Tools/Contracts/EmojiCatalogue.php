<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Contracts;

/**
 * An emoji catalogue console can consult beyond its own built-in map.
 *
 * Console owns this contract and never implements it: `laranail/emojis` (which requires console)
 * ships the implementation, and {@see \Simtabi\Laranail\Console\Tools\Support\EmojisBridge}
 * discovers it. Keeping the dependency pointed that way is what lets console stay a foundation
 * package with no reference to the catalogue's classes.
 */
interface EmojiCatalogue
{
    /**
     * The glyph for a shortcode name (no colons): the emoji itself when $unicode, a seven-bit
     * ASCII form otherwise. Null for a name the catalogue does not know.
     */
    public function glyph(string $name, bool $unicode): ?string;

    /**
     * Every shortcode name the catalogue resolves.
     *
     * @return list<string>
     */
    public function names(): array;

    /**
     * The text with each literal emoji rewritten as its `:shortcode:`, everything else unchanged.
     */
    public function toShortcodes(string $text): string;

    /**
     * Whether one grapheme cluster is a single emoji. Console asks only about clusters its own
     * width table under-measures, which in practice means codepoints newer than that table.
     */
    public function isEmoji(string $cluster): bool;
}
