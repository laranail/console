<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Widgets;

use Stringable;
use Simtabi\Laranail\Console\Tools\Support\Lang;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A two-column `Metric | Value` table — the statistics block most commands end
 * with — as a preset over {@see Table}, so the headers are translated once and
 * integer values are grouped (`12,345`) the same way everywhere.
 *
 *     MetricTable::make()
 *         ->metric('Total icons', 12345)
 *         ->metric('Database size', FileSize::format($bytes))
 *         ->render($output);
 *
 * @api Stable widget (SemVer-covered).
 */
final class MetricTable implements Stringable
{
    /** @var list<array{0: string, 1: string}> */
    private array $rows = [];

    private ?string $metricHeader = null;

    private ?string $valueHeader = null;

    public function __toString(): string
    {
        return $this->render();
    }

    public static function make(): self
    {
        return new self;
    }

    public function headers(?string $metric = null, ?string $value = null): self
    {
        $this->metricHeader = $metric;
        $this->valueHeader = $value;

        return $this;
    }

    public function metric(string $label, int|float|string|bool|null $value): self
    {
        $this->rows[] = [$label, $this->format($value)];

        return $this;
    }

    /**
     * Either `label => value`, or a list of `[label, value]` pairs -- the pair
     * form keeps two rows whose (translated) labels happen to be identical,
     * which a keyed array silently collapses into one.
     *
     * @param array<string, int|float|string|bool|null>|list<array{0: string, 1: int|float|string|bool|null}> $metrics
     */
    public function metrics(array $metrics): self
    {
        foreach ($metrics as $label => $value) {
            if (is_int($label) && is_array($value) && array_key_exists(0, $value) && array_key_exists(1, $value)) {
                $this->metric((string) $value[0], $value[1]);

                continue;
            }

            $this->metric((string) $label, is_array($value) ? null : $value);
        }

        return $this;
    }

    public function render(?OutputInterface $output = null): string
    {
        if ($this->rows === []) {
            return '';
        }

        return Table::make()
            ->headers([
                $this->metricHeader ?? Lang::get('widgets.metric_table.metric', 'Metric'),
                $this->valueHeader ?? Lang::get('widgets.metric_table.value', 'Value'),
            ])
            ->rows($this->rows)
            ->render($output);
    }

    private function format(int|float|string|bool|null $value): string
    {
        return match (true) {
            is_int($value)   => number_format($value),
            is_float($value) => rtrim(rtrim(number_format($value, 2), '0'), '.'),
            is_bool($value)  => $value ? Lang::get('widgets.metric_table.yes', 'Yes') : Lang::get('widgets.metric_table.no', 'No'),
            $value === null  => '—',
            default          => $value,
        };
    }
}
