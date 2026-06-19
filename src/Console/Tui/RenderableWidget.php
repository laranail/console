<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tui;

use Simtabi\Laranail\Console\Tools\Contracts\Renderable;
use Stringable;
use Symfony\Component\Tui\Render\RenderContext;
use Symfony\Component\Tui\Widget\AbstractWidget;

/**
 * Mounts any of our output widgets into a {@see Tui} app.
 *
 * Wraps a `Tools\Contracts\Renderable` (Panel/PanelBlock), or any `Stringable`
 * widget (Box, Table, Tree, Gauge, Sparkline, Banner, Summary, Header, Columns,
 * …), or a raw string, and emits its lines as a symfony/tui widget.
 *
 * Requires `symfony/tui` (PHP >= 8.4.1, experimental). See docs/tools/tui.md.
 */
final class RenderableWidget extends AbstractWidget
{
    /** @var list<string> */
    private readonly array $lines;

    public function __construct(Renderable|Stringable|string $content)
    {
        $this->lines = $this->normalize($content);
    }

    public static function of(Renderable|Stringable|string $content): self
    {
        return new self($content);
    }

    /**
     * The lines this widget contributes — pure, so it can be asserted without
     * booting the event loop.
     *
     * @return list<string>
     */
    public function toLines(): array
    {
        return $this->lines;
    }

    /**
     * @return list<string>
     */
    public function render(RenderContext $context): array
    {
        return $this->lines;
    }

    /**
     * @return list<string>
     */
    private function normalize(Renderable|Stringable|string $content): array
    {
        if ($content instanceof Renderable) {
            return $content->renderLines();
        }

        return explode("\n", (string) $content);
    }
}
