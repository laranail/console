<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support\Fixtures;

use RuntimeException;
use Simtabi\Laranail\Console\Tools\Contracts\EmojiCatalogue;

/**
 * A four-entry stand-in for laranail/emojis, which console only suggests. Its names deliberately
 * collide with console's map (`cross`, `stop`) the way the real catalogue's do.
 */
final readonly class FakeEmojiCatalogue implements EmojiCatalogue
{
    private const array GLYPHS = [
        'unicorn' => ['🦄', ':unicorn:'],
        'rocket'  => ['🚀', ':rocket:'],
        'cross'   => ['✝️', ':latin_cross:'],
        'stop'    => ['⏹️', ':stop_button:'],
    ];

    public function __construct(private bool $broken = false) {}

    public function glyph(string $name, bool $unicode): ?string
    {
        $this->failIfBroken();

        return self::GLYPHS[$name][$unicode ? 0 : 1] ?? null;
    }

    public function names(): array
    {
        $this->failIfBroken();

        return array_keys(self::GLYPHS);
    }

    public function toShortcodes(string $text): string
    {
        $this->failIfBroken();

        return strtr($text, ['🦄' => ':unicorn:', '🚀' => ':rocket:']);
    }

    public function isEmoji(string $cluster): bool
    {
        $this->failIfBroken();

        // U+1FAEA, a Unicode 17 emoji Symfony's width table predates.
        return $cluster === "\u{1FAEA}";
    }

    private function failIfBroken(): void
    {
        if ($this->broken) {
            throw new RuntimeException('catalogue failure');
        }
    }
}
