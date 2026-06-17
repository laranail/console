<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Commands\Services;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command Display Service
 *
 * Handles console output formatting, progress indicators,
 * and enhanced message display for console commands.
 */
class CommandDisplayService
{
    protected OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Display a success message with emoji
     */
    public function success(string $message): void
    {
        $this->output->writeln("✅ {$message}");
    }

    /**
     * Display a warning message with emoji
     */
    public function warning(string $message): void
    {
        $this->output->writeln("⚠️  {$message}");
    }

    /**
     * Display an error message with emoji
     */
    public function error(string $message): void
    {
        $this->output->writeln("❌ {$message}");
    }

    /**
     * Display an info message with emoji
     */
    public function info(string $message): void
    {
        $this->output->writeln("ℹ️  {$message}");
    }

    /**
     * Show a progress bar for long operations
     */
    public function showProgressBar(int $total, string $title = 'Processing'): ProgressBar
    {
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->setFormat("{$title}: %current%/%max% [%bar%] %percent:3s%% %message%");
        $progressBar->setMessage('Starting...');

        return $progressBar;
    }

    /**
     * Display a table with data
     */
    public function displayTable(array $headers, array $rows): void
    {
        $table = new Table($this->output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();
    }

    /**
     * Format bytes to human readable format
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Display a separator line
     */
    public function separator(string $char = '=', int $length = 50): void
    {
        $this->output->writeln(str_repeat($char, $length));
    }

    /**
     * Display a header with styling
     */
    public function header(string $title, string $char = '='): void
    {
        $this->separator($char);
        $this->output->writeln("<comment>{$title}</comment>");
        $this->separator($char);
    }

    /**
     * Display a list of items
     */
    public function list(array $items, string $title = ''): void
    {
        if ($title) {
            $this->output->writeln("<comment>{$title}</comment>");
        }

        foreach ($items as $item) {
            $this->output->writeln("  • {$item}");
        }
    }

    /**
     * Display key-value pairs
     */
    public function keyValue(array $data, string $title = ''): void
    {
        if ($title) {
            $this->output->writeln("<comment>{$title}</comment>");
        }

        foreach ($data as $key => $value) {
            $this->output->writeln("  <info>{$key}:</info> {$value}");
        }
    }
}
