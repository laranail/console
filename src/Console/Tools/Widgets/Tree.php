<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Symbols;

/**
 * Renders a nested tree with `├─ │ └─` connectors (ASCII fallbacks without
 * Unicode), each node optionally prefixed with a status glyph.
 */
final class Tree
{
    private string $label;

    /** @var list<Tree> */
    private array $children = [];

    private ?string $status = null;

    private readonly Symbols $symbols;

    public function __construct(string $label = '', ?Capabilities $capabilities = null)
    {
        $this->label = ConsoleUIFormatter::sanitizeText($label);
        $this->symbols = Symbols::for($capabilities ?? Capabilities::detect());
    }

    public static function make(string $label = ''): self
    {
        return new self($label);
    }

    /**
     * Add a child node. The optional callback receives the new child for nesting.
     */
    public function child(string $label, ?callable $build = null): self
    {
        $node = new self($label);

        if ($build !== null) {
            $build($node);
        }

        $this->children[] = $node;

        return $this;
    }

    /**
     * Set the status glyph (success/error/warning/info/pending/running) for this node.
     */
    public function status(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function render(): string
    {
        return rtrim($this->label() . "\n" . $this->renderChildren(''));
    }

    private function renderChildren(string $prefix): string
    {
        $out = '';
        $last = count($this->children) - 1;

        foreach ($this->children as $i => $child) {
            $isLast = $i === $last;
            $connector = $this->symbols->get($isLast ? 'last' : 'branch');
            $stem = $isLast ? $this->symbols->get('gap') : $this->symbols->get('stem');

            $out .= $prefix . $connector . ' ' . $child->label() . "\n";
            $out .= $child->renderChildren($prefix . $stem);
        }

        return $out;
    }

    private function label(): string
    {
        return $this->status !== null
            ? $this->symbols->get($this->status) . ' ' . $this->label
            : $this->label;
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
