<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Stringable;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A fluent table over Symfony's Table helper with named style presets (incl. a
 * GitHub-flavoured markdown emitter), plus two layout variants:
 *
 *  - grouped(): segment rows under labelled group headers in one frame.
 *  - tree():    an indented hierarchy column for nested rows.
 *
 * Falls back to the ASCII style without Unicode support.
 */
final class Table implements Stringable
{
    /** @var array<string, string> preset => Symfony built-in style */
    private const array STYLES = [
        'ascii' => 'default',
        'light' => 'box',
        'double' => 'box-double',
        'compact' => 'compact',
        'borderless' => 'borderless',
        'markdown' => 'markdown',
    ];

    /** @var list<string> */
    private array $headers = [];

    /** @var list<list<string>> */
    private array $rows = [];

    /** @var array<string, list<list<string>>> */
    private array $groups = [];

    /** @var list<array{0:int,1:list<string>}> depth + cells */
    private array $treeRows = [];

    private string $mode = 'plain';

    private string $style = 'light';

    public function __construct(private readonly Capabilities $capabilities = new Capabilities) {}

    public static function make(): self
    {
        return new self;
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
        $this->mode = 'plain';

        return $this;
    }

    /**
     * Segment rows under labelled group headers within a single frame.
     *
     * @param array<string, list<list<string>>> $groups label => rows
     */
    public function grouped(array $groups): self
    {
        $this->groups = $groups;
        $this->mode = 'grouped';

        return $this;
    }

    /**
     * Render an indented hierarchy in the first column.
     *
     * @param list<array{0:int,1:list<string>}> $rows [depth, cells] per row
     */
    public function tree(array $rows): self
    {
        $this->treeRows = $rows;
        $this->mode = 'tree';

        return $this;
    }

    public function style(string $style): self
    {
        $this->style = isset(self::STYLES[$style]) ? $style : 'light';

        return $this;
    }

    public function render(?OutputInterface $output = null): string
    {
        $buffer = new BufferedOutput;
        $style = $this->capabilities->supportsUnicode() ? $this->style : 'ascii';

        $table = new SymfonyTable($buffer);
        $table->setStyle(self::STYLES[$style]);

        if ($this->headers !== []) {
            $table->setHeaders($this->headers);
        }

        $table->setRows($this->buildRows());
        $table->render();

        $rendered = $buffer->fetch();

        $output?->write($rendered);

        return $rendered;
    }

    /**
     * @return list<list<string>|TableCell[]|TableSeparator>
     */
    private function buildRows(): array
    {
        return match ($this->mode) {
            'grouped' => $this->buildGroupedRows(),
            'tree' => $this->buildTreeRows(),
            default => $this->rows,
        };
    }

    /**
     * @return list<list<string>|TableCell[]|TableSeparator>
     */
    private function buildGroupedRows(): array
    {
        // Span the full table: the widest of the header row and every group row.
        $columns = count($this->headers);
        foreach ($this->groups as $rows) {
            foreach ($rows as $row) {
                $columns = max($columns, count($row));
            }
        }
        $columns = max($columns, 1);

        $built = [];
        $first = true;

        foreach ($this->groups as $label => $rows) {
            if (! $first) {
                $built[] = new TableSeparator;
            }

            $built[] = [new TableCell((string) $label, ['colspan' => $columns])];

            foreach ($rows as $row) {
                $built[] = $row;
            }

            $first = false;
        }

        return $built;
    }

    /**
     * @return list<list<string>>
     */
    private function buildTreeRows(): array
    {
        $unicode = $this->capabilities->supportsUnicode();
        $stem = $unicode ? '└─ ' : '`- ';
        $built = [];

        foreach ($this->treeRows as [$depth, $cells]) {
            $indent = $depth > 0 ? str_repeat('   ', $depth - 1) . $stem : '';
            $cells[0] = $indent . ($cells[0] ?? '');
            $built[] = $cells;
        }

        return $built;
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
