<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Formatting;

use Illuminate\Console\OutputStyle;
use Illuminate\Support\Str;
use Simtabi\Laranail\ConsoleTools\Formatting\Contracts\SeederConsoleFormatterInterface;

/**
 * Seeder Console Formatter
 *
 * Provides tree-structured console output with status symbols, color coding,
 * and statistics tracking for database seeding operations.
 *
 * SOLID Principle: Single Responsibility - Only handles console output formatting
 */
class SeederConsoleFormatter implements SeederConsoleFormatterInterface
{
    private const array DEFAULT_PADDING = [
        'info' => 2,
        'discovery' => 2,
        'group' => 2,
        'seeder_increment' => 4,
        'error_detail' => 4,
        'summary' => 2,
    ];

    private const array DEFAULT_DISPLAY = [
        'show_duration' => true,
        'show_status_symbols' => true,
        'show_tree_structure' => true,
        'show_group_headers' => true,
        'show_summary' => true,
        'show_error_details' => true,
    ];

    private const array DEFAULT_DISPLAY_WIDTHS = [
        'tree_symbol' => 3,
        'status_symbol' => 1,
        'spaces' => 4,
        'duration_suffix' => 3,
        'done_text' => 5,
    ];

    private ?OutputStyle $output = null;

    private ConsoleUIFormatter $formatter;

    private array $statistics = [];

    private float $startTime = 0.0;

    private array $padding;

    private array $display;

    private array $displayWidths;

    public function __construct(array $config = [])
    {
        $this->padding = array_merge(self::DEFAULT_PADDING, $config['padding'] ?? []);
        $this->display = array_merge(self::DEFAULT_DISPLAY, $config['display'] ?? []);
        $this->displayWidths = array_merge(self::DEFAULT_DISPLAY_WIDTHS, $config['displayWidths'] ?? []);

        $this->formatter = ConsoleUIFormatter::create()
            ->configure([
                'padding' => $this->padding,
                'display' => $this->display,
                'displayWidths' => $this->displayWidths,
            ]);

        $this->initializeConfiguration();
        $this->resetStatistics();
    }

    private function initializeConfiguration(): void
    {
        $this->padding['seeder'] = $this->padding['group'] + $this->padding['seeder_increment'];

        foreach ($this->padding as $key => $value) {
            if (is_numeric($value)) {
                $this->padding[$key] = Str::repeat(' ', (int) $value);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function initializeSession(): void
    {
        $this->formatter->startSession();
        $this->startTime = microtime(true);
        $this->resetStatistics();
    }

    /**
     * {@inheritDoc}
     */
    public function displayGroupHeader(string $groupName, int $seederCount, bool $isLast = false): void
    {
        $groupLine = ConsoleUIFormatter::header($groupName, $seederCount, 'seeders', $isLast);
        $this->writeLine($this->padding['group'] . $groupLine);
        $this->updateStatistics('groups');
    }

    /**
     * {@inheritDoc}
     */
    public function displaySeederStart(string $seederClass, bool $isLast = false): void
    {
        $seederLine = ConsoleUIFormatter::statusLine($seederClass, 'RUNNING', '', $isLast);
        $this->write($this->padding['seeder'] . $seederLine . "\r");
    }

    /**
     * {@inheritDoc}
     */
    public function displaySeederSuccess(string $seederClass, float $duration, bool $isLast = false): void
    {
        $durationMs = number_format($duration * 1000, 2);
        $seederName = $this->extractSeederName($seederClass);

        $seederLine = ConsoleUIFormatter::statusLine($seederName, 'DONE', $durationMs, $isLast);
        $dotPadding = $this->getDotPadding($seederName, $durationMs);

        $line = $this->padding['seeder'] . str_replace($durationMs . 'ms', $dotPadding . $durationMs . 'ms', $seederLine);
        $this->writeLine($line);
        $this->updateStatistics('successful');
    }

    /**
     * {@inheritDoc}
     */
    public function displaySeederError(string $seederClass, \Exception $exception, float $duration, bool $isLast = false): void
    {
        $durationMs = number_format($duration * 1000);
        $seederLine = ConsoleUIFormatter::statusLine($seederClass, 'FAILED', $durationMs, $isLast);

        $this->writeLine($this->padding['seeder'] . $seederLine);
        $this->displayErrorDetails($exception);
        $this->updateStatistics('failed');
    }

    /**
     * {@inheritDoc}
     */
    public function displaySeederSkipped(string $seederClass, string $reason, bool $isLast = false): void
    {
        $seederLine = ConsoleUIFormatter::statusLine($seederClass, 'SKIPPED', '', $isLast, $reason);
        $this->writeLine($this->padding['seeder'] . $seederLine);
        $this->updateStatistics('skipped');
    }

    /**
     * {@inheritDoc}
     */
    public function displaySummary(): void
    {
        if (! $this->display['show_summary']) {
            return;
        }

        $this->writeLine('');
        $this->displaySummaryStatistics();
        $this->displayFinalStatus();
    }

    private function displayErrorDetails(\Exception $exception): void
    {
        if (! $this->display['show_error_details']) {
            return;
        }

        $errorLine = $this->padding['error_detail'] . $this->formatter->colorize(
            'Error: ' . $exception->getMessage(),
            ConsoleUIFormatter::RED
        );
        $this->writeLine($errorLine);
    }

    private function displaySummaryStatistics(): void
    {
        $this->writeLine(ConsoleUIFormatter::statisticsLine('Groups', $this->statistics['groups'], 'group', ConsoleUIFormatter::CYAN));
        $this->writeLine(ConsoleUIFormatter::statisticsLine('Successful', $this->statistics['successful'], 'success', ConsoleUIFormatter::GREEN));

        if ($this->statistics['failed'] > 0) {
            $this->writeLine(ConsoleUIFormatter::statisticsLine('Failed', $this->statistics['failed'], 'error', ConsoleUIFormatter::RED));
        }

        if ($this->statistics['skipped'] > 0) {
            $this->writeLine(ConsoleUIFormatter::statisticsLine('Skipped', $this->statistics['skipped'], 'skipped', ConsoleUIFormatter::YELLOW));
        }

        $this->writeLine('');
    }

    private function displayFinalStatus(): void
    {
        $total = $this->statistics['successful'] + $this->statistics['failed'] + $this->statistics['skipped'];
        $duration = microtime(true) - $this->startTime;

        if ($this->statistics['failed'] === 0) {
            $status = $this->formatter->colorize('All seeders completed successfully!', ConsoleUIFormatter::GREEN, true);
        } else {
            $status = $this->formatter->colorize(
                "Completed with {$this->statistics['failed']} failures out of {$total} seeders",
                ConsoleUIFormatter::YELLOW,
                true
            );
        }

        $this->writeLine($this->padding['summary'] . $status);
        $this->writeLine($this->padding['summary'] . $this->formatter->colorize(
            'Total execution time: ' . number_format($duration * 1000, 2) . 'ms',
            ConsoleUIFormatter::GRAY
        ));
    }

    private function updateStatistics(string $type): void
    {
        $this->statistics[$type] = ($this->statistics[$type] ?? 0) + 1;
        $this->formatter->updateStatistics($type);
    }

    private function writeLine(string $line): void
    {
        if ($this->output) {
            $this->output->writeln($line);
        }
    }

    private function write(string $text): void
    {
        if ($this->output) {
            $this->output->write($text);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function writeInfo(string $message): void
    {
        $badge = ConsoleUIFormatter::badge('INFO', ConsoleUIFormatter::BADGE_STYLE_INFO);
        $this->writeLine($this->padding['info'] . $badge . ' ' . $this->formatter->colorize($message, ConsoleUIFormatter::BLUE));
    }

    /**
     * {@inheritDoc}
     */
    public function writeDiscovery(string $message): void
    {
        $badge = ConsoleUIFormatter::badge('DISCOVERY', ConsoleUIFormatter::BADGE_STYLE_INFO);
        $this->writeLine($this->padding['discovery'] . $badge . ' ' . $this->formatter->colorize($message, ConsoleUIFormatter::CYAN));
    }

    /**
     * {@inheritDoc}
     */
    public function writeSuccess(string $message): void
    {
        $badge = ConsoleUIFormatter::badge('SUCCESS', ConsoleUIFormatter::BADGE_STYLE_SUCCESS);
        $this->writeLine($this->padding['info'] . $badge . ' ' . $this->formatter->colorize($message, ConsoleUIFormatter::GREEN));
    }

    /**
     * {@inheritDoc}
     */
    public function writeWarning(string $message): void
    {
        $badge = ConsoleUIFormatter::badge('WARNING', ConsoleUIFormatter::BADGE_STYLE_WARNING);
        $this->writeLine($this->padding['info'] . $badge . ' ' . $this->formatter->colorize($message, ConsoleUIFormatter::YELLOW));
    }

    /**
     * {@inheritDoc}
     */
    public function writeError(string $message): void
    {
        $badge = ConsoleUIFormatter::badge('ERROR', ConsoleUIFormatter::BADGE_STYLE_DANGER);
        $this->writeLine($this->padding['info'] . $badge . ' ' . $this->formatter->colorize($message, ConsoleUIFormatter::RED));
    }

    private function extractSeederName(string $seederClass): string
    {
        $parts = Str::of($seederClass)->explode('\\');
        $className = $parts->last();

        if (Str::endsWith($className, 'Seeder')) {
            $className = Str::substr($className, 0, -7);
        }

        return $className;
    }

    private function getDotPadding(string $seederName, string $duration): string
    {
        $maxWidth = 50;

        $usedWidth =
            $this->displayWidths['tree_symbol'] +
            $this->displayWidths['status_symbol'] +
            $this->displayWidths['spaces'] +
            Str::length($duration) + $this->displayWidths['duration_suffix'] +
            $this->displayWidths['done_text'];

        $availableWidth = $maxWidth - $usedWidth;
        $seederNameWidth = Str::length($seederName);

        if ($seederNameWidth >= $availableWidth) {
            return ' ';
        }

        $dotCount = $availableWidth - $seederNameWidth;

        return Str::repeat('.', max(1, $dotCount));
    }

    /**
     * {@inheritDoc}
     */
    public function resetStatistics(): void
    {
        $this->statistics = [
            'groups' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];
        $this->formatter->resetStatistics();
    }

    /**
     * {@inheritDoc}
     */
    public function setOutput(?OutputStyle $output): void
    {
        $this->output = $output;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * {@inheritDoc}
     */
    public function getSessionDuration(): float
    {
        return microtime(true) - $this->startTime;
    }
}
