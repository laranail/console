<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Notifications;

use Simtabi\Laranail\ConsoleTools\Notifications\Contracts\ConsoleChannelInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Standalone console notification channel.
 *
 * Writes timestamped, optionally data-annotated messages to a Symfony
 * console output (or `echo` when none is provided). Self-contained — it
 * does not depend on any host notification base class.
 */
final class ConsoleChannel implements ConsoleChannelInterface
{
    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [], private readonly ?OutputInterface $output = null)
    {
        $this->config = array_merge(['enabled' => true, 'show_data' => true], $config);
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? true);
    }

    public function getName(): string
    {
        return 'console';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function send(string $message, array $data = []): bool
    {
        $formatted = $this->formatMessage($message, $data);

        if ($this->output instanceof OutputInterface) {
            $level = is_string($data['level'] ?? null) ? $data['level'] : 'info';
            $this->output->writeln("<{$level}>{$formatted}</{$level}>");
        } else {
            echo $formatted . "\n";
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function formatMessage(string $message, array $data): string
    {
        $output = '[' . date('Y-m-d H:i:s') . "] {$message}";

        if ($data !== [] && ($this->config['show_data'] ?? true)) {
            $output .= ' | Data: ' . (json_encode($data) ?: '');
        }

        return $output;
    }
}
