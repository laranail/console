<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A fluent table over Symfony's Table helper with named style presets,
 * including a GitHub-flavoured markdown emitter. Falls back to the ASCII style
 * without Unicode support.
 */
final class Table
{
    /** @var array<string, string> preset => Symfony built-in style */
    private const STYLES = [
        'ascii'      => 'default',
        'light'      => 'box',
        'double'     => 'box-double',
        'compact'    => 'compact',
        'borderless' => 'borderless',
        'markdown'   => 'markdown',
    ];

    /** @var list<string> */
    private array $headers = [];

    /** @var list<list<string>> */
    private array $rows = [];

    private string $style = 'light';

    public function __construct(private readonly Capabilities $capabilities = new Capabilities()) {}

    public static function make(): self
    {
        return new self();
    }

    /**
     * @param list<string> $headers
     */
    public function headers(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param list<list<string>> $rows
     */
    public function rows(array $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function style(string $style): self
    {
        $this->style = isset(self::STYLES[$style]) ? $style : 'light';

        return $this;
    }

    public function render(?OutputInterface $output = null): string
    {
        $buffer = new BufferedOutput();
        $style = $this->capabilities->supportsUnicode() ? $this->style : 'ascii';

        $table = new SymfonyTable($buffer);
        $table->setStyle(self::STYLES[$style]);
        $table->setHeaders($this->headers);
        $table->setRows($this->rows);
        $table->render();

        $rendered = $buffer->fetch();

        $output?->write($rendered);

        return $rendered;
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
