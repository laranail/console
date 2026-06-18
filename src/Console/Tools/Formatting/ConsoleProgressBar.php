<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Formatting;

use Countable;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsoleProgressBar
{
    public ?ConsoleOutput $consoleOutput;

    public ?ProgressBar $progressBar;

    public SymfonyStyle $style;

    protected ?string $taskLabel = '';

    protected string $spaceFormatting = '  ';

    protected ConsoleUIFormatter $formatter;

    public function __construct()
    {
        $this->reset();
        $this->consoleOutput = new ConsoleOutput;
        $this->formatter = ConsoleUIFormatter::create();
    }

    public function setTaskLabel(?string $taskLabel): static
    {
        $this->taskLabel = $taskLabel;

        return $this;
    }

    /**
     * Set task label with custom formatting using ConsoleUIFormatter
     *
     * @param string|null $taskLabel The task label
     * @param string|null $color Optional color for the label
     * @param array $styles Optional text styles for the label
     */
    public function setTaskLabelWithFormatting(?string $taskLabel, ?string $color = null, array $styles = []): static
    {
        $this->taskLabel = $taskLabel;

        if ($taskLabel && ($color || $styles !== [])) {
            $this->formatter
                ->reset()
                ->addMessage($taskLabel);

            if ($color) {
                $this->formatter->addTextColor($color);
            }

            if ($styles !== []) {
                $this->formatter->addTextStyles($styles);
            }
        }

        return $this;
    }

    public function getTaskLabel(): ?string
    {
        return $this->taskLabel;
    }

    private function reset(): void
    {
        $this->progressBar = null;
        $this->taskLabel = null;
    }

    /**
     * Count the total number of items in a given array or object
     *
     *
     * @return object|int
     */
    private function count(array|object|int $data): int
    {
        if (is_array($data)) {
            return count($data);
        }

        if ($data instanceof Countable) {
            return count($data);
        }

        if (is_object($data)) {
            return count(get_object_vars($data));
        }

        return $data;
    }

    public function iterate(object|array $data): iterable
    {
        return $this->progressBar->iterate($data);
    }

    private function generateProgressBar(array|object|int $data): static
    {

        // create a progress bar instance if it doesn't exist yet
        $this->progressBar = new ProgressBar($this->consoleOutput, $this->count($data));

        // define the placeholder format using ConsoleUIFormatter
        ProgressBar::setPlaceholderFormatterDefinition('memory', function (ProgressBar $bar): string {
            static $i = 0;
            $memory = 100000 * $i;
            $color = $i++ ? ConsoleUIFormatter::RED : ConsoleUIFormatter::BLUE;
            $bgColor = $i ? ConsoleUIFormatter::BG_RED : ConsoleUIFormatter::BG_BLUE;

            return $this->formatter
                ->reset()
                ->addMessage(Helper::formatMemory($memory))
                ->addTextColor(ConsoleUIFormatter::WHITE)
                ->addBackgroundColor($bgColor)
                ->render();
        });

        // set formats using ConsoleUIFormatter for consistent styling
        $this->progressBar->setFormat('debug');

        // Create formatted progress bar string using ConsoleUIFormatter
        $progressFormat = $this->formatter
            ->reset()
            ->addMessage('%current%/%max% [%bar%] | %percent%% %remaining% | %elapsed:6s% / %estimated:-6s% | %memory:6s%')
            ->addTextColor(ConsoleUIFormatter::GRAY)
            ->render();

        $this->progressBar->setFormat("\r{$this->spaceFormatting}{$progressFormat}");

        // redraw
        $this->progressBar->setRedrawFrequency(100);
        $this->progressBar->maxSecondsBetweenRedraws(0.2);
        $this->progressBar->minSecondsBetweenRedraws(0.1);

        // starts and displays the progress bar
        $this->progressBar->start();

        return $this;
    }

    public function startProgressBar(?string $label, object|array|int $data): static
    {
        return $this->setTaskLabel($label)->generateProgressBar($data);
    }

    public function advanceProgressBar(int $step = 1, int $sleep = 1500): static
    {
        if ($step < 1) {
            $step = 1;
        }

        $this->progressBar->advance($step);
        usleep($sleep);

        return $this;
    }

    public function finishProgressBar(string $message = '', bool $newline = true): void
    {

        // set task label using ConsoleUIFormatter for consistent formatting
        $label = $this->taskLabel ? Str::ucfirst(Str::lower($this->taskLabel)) : '';

        $this->progressBar->finish();

        // Format the completion message using ConsoleUIFormatter
        $completionMessage = $this->formatter
            ->reset()
            ->addMessage($message)
            ->addTextColor(ConsoleUIFormatter::GREEN)
            ->addTextStyles(ConsoleUIFormatter::BOLD)
            ->render();

        $formattedMessage = $message === '' || $message === '0' ? '' : "| {$completionMessage}";

        // Format the label using ConsoleUIFormatter
        $formattedLabel = $this->formatter
            ->reset()
            ->addMessage($label)
            ->addTextColor(ConsoleUIFormatter::GRAY)
            ->render();

        $this->consoleOutput->write(" | {$formattedLabel} {$formattedMessage} \r");

        if ($newline) {
            $this->consoleOutput->write('', true);
        }

        $this->reset();
    }

    public function getConsoleOutput(): ?ConsoleOutput
    {
        return $this->consoleOutput;
    }

    /**
     * Get the ConsoleUIFormatter instance for advanced formatting
     */
    public function getFormatter(): ConsoleUIFormatter
    {
        return $this->formatter;
    }

    /**
     * Display a status message during progress using ConsoleUIFormatter
     *
     * @param string $message The status message
     * @param string $status The status type (success, error, warning, info)
     * @param bool $newline Whether to add a newline after the message
     */
    public function displayStatus(string $message, string $status = 'info', bool $newline = true): static
    {
        $formattedMessage = match (strtolower($status)) {
            'success' => ConsoleUIFormatter::success($message),
            'error' => ConsoleUIFormatter::error($message),
            'warning' => ConsoleUIFormatter::warning($message),
            'info' => ConsoleUIFormatter::info($message),
            default => $this->formatter
                ->reset()
                ->addMessage($message)
                ->addTextColor(ConsoleUIFormatter::GRAY)
                ->render()
        };

        $this->consoleOutput->write($formattedMessage . ($newline ? "\n" : ''));

        return $this;
    }

    /**
     * Display a badge-style status using ConsoleUIFormatter
     *
     * @param string $message The message to display
     * @param string $badgeStyle The badge style to use
     * @param bool $newline Whether to add a newline after the message
     */
    public function displayBadge(string $message, string $badgeStyle = ConsoleUIFormatter::BADGE_STYLE_INFO, bool $newline = true): static
    {
        $badge = ConsoleUIFormatter::badge($message, $badgeStyle);
        $this->consoleOutput->write($badge . ($newline ? "\n" : ''));

        return $this;
    }

    /**
     * Finish progress bar with a badge-style completion message
     *
     * @param string $message The completion message
     * @param string $badgeStyle The badge style for the completion message
     * @param bool $newline Whether to add a newline after the message
     */
    public function finishProgressBarWithBadge(string $message = '', string $badgeStyle = ConsoleUIFormatter::BADGE_STYLE_SUCCESS, bool $newline = true): void
    {
        $label = $this->taskLabel ? Str::ucfirst(Str::lower($this->taskLabel)) : '';

        $this->progressBar->finish();

        // Format the label
        $formattedLabel = $this->formatter
            ->reset()
            ->addMessage($label)
            ->addTextColor(ConsoleUIFormatter::GRAY)
            ->render();

        // Create badge for completion message
        $completionBadge = $message === '' || $message === '0' ? '' : ConsoleUIFormatter::badge($message, $badgeStyle);

        $this->consoleOutput->write(" | {$formattedLabel} {$completionBadge} \r");

        if ($newline) {
            $this->consoleOutput->write('', true);
        }

        $this->reset();
    }
}
