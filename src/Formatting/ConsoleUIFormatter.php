<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Formatting;

use Illuminate\Support\Str;
use Stringable;
use Throwable;

/**
 * Fluent helper class for formatting Symfony Console output with badge support
 *
 * @example
 * // Regular formatting
 * $output = ConsoleUIFormatter::create()
 *     ->addMessage('Processing...')
 *     ->addTextColor(ConsoleUIFormatter::GREEN)
 *     ->render();
 *
 * // Badge formatting
 * $badge = ConsoleUIFormatter::create()
 *     ->addMessage('NEW')
 *     ->isBadge(ConsoleUIFormatter::BADGE_STYLE_SUCCESS)
 *     ->render();
 */
class ConsoleUIFormatter implements Stringable
{
    // Foreground Colors
    public const string BLACK = 'black';

    public const string RED = 'red';

    public const string GREEN = 'green';

    public const string YELLOW = 'yellow';

    public const string BLUE = 'blue';

    public const string MAGENTA = 'magenta';

    public const string CYAN = 'cyan';

    public const string WHITE = 'white';

    public const string GRAY = 'gray';

    public const string BRIGHT_RED = 'bright-red';

    public const string BRIGHT_GREEN = 'bright-green';

    public const string BRIGHT_YELLOW = 'bright-yellow';

    public const string BRIGHT_BLUE = 'bright-blue';

    public const string BRIGHT_MAGENTA = 'bright-magenta';

    public const string BRIGHT_CYAN = 'bright-cyan';

    public const string BRIGHT_WHITE = 'bright-white';

    // Background Colors (prefixed for clarity)
    public const string BG_BLACK = 'black';

    public const string BG_RED = 'red';

    public const string BG_GREEN = 'green';

    public const string BG_YELLOW = 'yellow';

    public const string BG_BLUE = 'blue';

    public const string BG_MAGENTA = 'magenta';

    public const string BG_CYAN = 'cyan';

    public const string BG_WHITE = 'white';

    public const string BG_GRAY = 'gray';

    public const string BG_BRIGHT_RED = 'bright-red';

    public const string BG_BRIGHT_GREEN = 'bright-green';

    public const string BG_BRIGHT_YELLOW = 'bright-yellow';

    public const string BG_BRIGHT_BLUE = 'bright-blue';

    public const string BG_BRIGHT_MAGENTA = 'bright-magenta';

    public const string BG_BRIGHT_CYAN = 'bright-cyan';

    public const string BG_BRIGHT_WHITE = 'bright-white';

    // Text Style Options
    public const string BOLD = 'bold';

    public const string UNDERSCORE = 'underscore';

    public const string UNDERLINE = 'underscore'; // Alias for underscore

    public const string BLINK = 'blink';

    public const string REVERSE = 'reverse';

    public const string CONCEAL = 'conceal';

    public const string HIDDEN = 'conceal'; // Alias for conceal

    // Predefined Style Tags
    public const string STYLE_TAG_INFO = 'info';

    public const string STYLE_TAG_COMMENT = 'comment';

    public const string STYLE_TAG_QUESTION = 'question';

    public const string STYLE_TAG_ERROR = 'error';

    // Badge Styles (Bootstrap-inspired)
    public const string BADGE_STYLE_PRIMARY = 'primary';

    public const string BADGE_STYLE_SECONDARY = 'secondary';

    public const string BADGE_STYLE_SUCCESS = 'success';

    public const string BADGE_STYLE_DANGER = 'danger';

    public const string BADGE_STYLE_WARNING = 'warning';

    public const string BADGE_STYLE_INFO = 'info';

    public const string BADGE_STYLE_LIGHT = 'light';

    public const string BADGE_STYLE_DARK = 'dark';

    // Tree Structure Symbols
    public const array TREE_SYMBOLS = [
        'success' => '✓',
        'error' => '✗',
        'running' => '⊙',
        'skipped' => '⊘',
        'warning' => '⚠',
        'info' => 'ℹ',
        'package' => '📦',
        'tree_mid' => '├─',
        'tree_last' => '└─',
        'tree_pipe' => '│',
        'tree_space' => '  ',
    ];

    // ANSI Color Codes (for terminal compatibility)
    public const array ANSI_COLORS = [
        // Foreground colors
        'black' => "\033[30m",
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'gray' => "\033[90m",
        'bright_red' => "\033[91m",
        'bright_green' => "\033[92m",
        'bright_yellow' => "\033[93m",
        'bright_blue' => "\033[94m",
        'bright_magenta' => "\033[95m",
        'bright_cyan' => "\033[96m",
        'bright_white' => "\033[97m",

        // Background colors
        'black_bg' => "\033[40m",
        'red_bg' => "\033[41m",
        'green_bg' => "\033[42m",
        'yellow_bg' => "\033[43m",
        'blue_bg' => "\033[44m",
        'magenta_bg' => "\033[45m",
        'cyan_bg' => "\033[46m",
        'white_bg' => "\033[47m",
        'gray_bg' => "\033[100m",
        'bright_red_bg' => "\033[101m",
        'bright_green_bg' => "\033[102m",
        'bright_yellow_bg' => "\033[103m",
        'bright_blue_bg' => "\033[104m",
        'bright_magenta_bg' => "\033[105m",
        'bright_cyan_bg' => "\033[106m",
        'bright_white_bg' => "\033[107m",

        // Text styles
        'bold' => "\033[1m",
        'dim' => "\033[2m",
        'italic' => "\033[3m",
        'underline' => "\033[4m",
        'reset' => "\033[0m",
    ];

    // Badge color schemes
    private const array BADGE_SCHEMES = [
        self::BADGE_STYLE_PRIMARY => [
            'fg' => self::WHITE,
            'bg' => '#0d6efd', // Bootstrap primary blue
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_SECONDARY => [
            'fg' => self::WHITE,
            'bg' => '#6c757d', // Bootstrap secondary gray
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_SUCCESS => [
            'fg' => self::WHITE,
            'bg' => '#198754', // Bootstrap success green
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_DANGER => [
            'fg' => self::WHITE,
            'bg' => '#dc3545', // Bootstrap danger red
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_WARNING => [
            'fg' => self::BLACK,
            'bg' => '#ffc107', // Bootstrap warning yellow
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_INFO => [
            'fg' => self::BLACK,
            'bg' => '#0dcaf0', // Bootstrap info cyan
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_LIGHT => [
            'fg' => self::BLACK,
            'bg' => '#f8f9fa', // Bootstrap light gray
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_DARK => [
            'fg' => self::WHITE,
            'bg' => '#212529', // Bootstrap dark
            'styles' => [self::BOLD],
        ],
    ];

    // Properties
    private string $message = '';

    private ?string $foregroundColor = null;

    private ?string $backgroundColor = null;

    private array $textStyles = [];

    private ?string $styleTag = null;

    private ?string $href = null;

    private bool $isClickable = false;

    private bool $badgeMode = false;

    private string $badgePadding = ' ';

    // Terminal capability detection
    private bool $supportsColor = true;

    private bool $supportsUnicode = true;

    // Configuration arrays
    private array $config = [
        'symbols' => [],
        'colors' => [],
        'padding' => [],
        'display' => [],
        'displayWidths' => [],
    ];

    // Session management
    private array $statistics = [];

    private float $startTime = 0.0;

    /**
     * Private constructor to enforce factory method usage
     */
    private function __construct()
    {
        $this->supportsColor = $this->detectColorSupport();
        $this->supportsUnicode = $this->detectUnicodeSupport();
        $this->resetStatistics();
    }

    /**
     * Create a new instance with fresh state
     */
    public static function create(): self
    {
        return new self;
    }

    /**
     * Set the message content
     */
    public function addMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Add text/foreground color with optional predefined style tag or clickable link
     *
     * @param string $text Color name or hex code (e.g., '#ff0000')
     * @param string|null $styleTag Optional predefined style tag (info, comment, question, error)
     * @param bool $isClickable Whether the text should be clickable (requires href)
     * @param string|null $href URL for clickable text
     */
    public function addTextColor(
        string $text,
        ?string $styleTag = null,
        bool $isClickable = false,
        ?string $href = null
    ): self {
        $this->foregroundColor = $text;
        $this->styleTag = $styleTag;
        $this->isClickable = $isClickable;

        if ($isClickable && $href) {
            $this->href = $href;
        }

        return $this;
    }

    /**
     * Add background color
     *
     * @param string $text Color name or hex code (e.g., '#2c3e50')
     */
    public function addBackgroundColor(string $text): self
    {
        $this->backgroundColor = $text;

        return $this;
    }

    /**
     * Add text style options
     *
     * @param string|array $styles Single style or array of styles
     */
    public function addTextStyles(string|array $styles): self
    {
        if (is_string($styles)) {
            $styles = [$styles];
        }

        $this->textStyles = array_unique(array_merge($this->textStyles, $styles));

        return $this;
    }

    /**
     * Enable badge mode with specified style
     *
     * @param string|null $style Badge style constant (BADGE_STYLE_*)
     * @param string $padding Padding character(s) around badge text
     */
    public function isBadge(?string $style = self::BADGE_STYLE_PRIMARY, string $padding = ' '): self
    {
        $this->badgeMode = true;
        $this->badgePadding = $padding;

        // Apply badge color scheme if style is specified
        if ($style && isset(self::BADGE_SCHEMES[$style])) {
            $scheme = self::BADGE_SCHEMES[$style];
            $this->foregroundColor = $scheme['fg'];
            $this->backgroundColor = $scheme['bg'];
            $this->textStyles = array_unique(array_merge($this->textStyles, $scheme['styles']));
        }

        return $this;
    }

    /**
     * Set clickable link
     *
     * @param string $url URL to make the text clickable
     */
    public function setHref(string $url): self
    {
        $this->href = $url;
        $this->isClickable = true;

        return $this;
    }

    /**
     * Clear all text styles
     */
    public function clearStyles(): self
    {
        $this->textStyles = [];

        return $this;
    }

    /**
     * Clear all formatting
     */
    public function reset(): self
    {
        $this->message = '';
        $this->foregroundColor = null;
        $this->backgroundColor = null;
        $this->textStyles = [];
        $this->styleTag = null;
        $this->href = null;
        $this->isClickable = false;
        $this->badgeMode = false;
        $this->badgePadding = ' ';

        return $this;
    }

    /**
     * Configure the formatter with custom settings
     */
    public function configure(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * Get tree symbol with fallback for non-Unicode terminals
     */
    public function getTreeSymbol(string $key): string
    {
        if (! $this->supportsUnicode) {
            return match ($key) {
                'success' => '+',
                'error' => 'x',
                'running' => 'o',
                'skipped' => '-',
                'warning' => '!',
                'info' => 'i',
                'package' => '*',
                'tree_mid' => '|-',
                'tree_last' => '`-',
                'tree_pipe' => '|',
                'tree_space' => '  ',
                default => self::TREE_SYMBOLS[$key] ?? '',
            };
        }

        return self::TREE_SYMBOLS[$key] ?? '';
    }

    /**
     * Create a tree line with proper symbols
     */
    public function treeLine(string $text, bool $isLast = false): string
    {
        $treeSymbol = $isLast ? $this->getTreeSymbol('tree_last') : $this->getTreeSymbol('tree_mid');

        return $treeSymbol . ' ' . $text;
    }

    /**
     * Colorize text with ANSI codes (for terminal compatibility)
     */
    public function colorize(string $text, string $color, bool $bold = false, ?string $background = null): string
    {
        if (! $this->supportsColor) {
            return $text;
        }

        $colorCode = self::ANSI_COLORS[$color] ?? '';
        $boldCode = $bold ? self::ANSI_COLORS['bold'] : '';
        $backgroundCode = $background ? self::ANSI_COLORS[$background] : '';
        $resetCode = self::ANSI_COLORS['reset'];

        return "{$boldCode}{$backgroundCode}{$colorCode}{$text}{$resetCode}";
    }

    /**
     * Start a new session for statistics tracking
     */
    public function startSession(): self
    {
        $this->startTime = microtime(true);
        $this->resetStatistics();

        return $this;
    }

    /**
     * Update statistics
     */
    public function updateStatistics(string $type, int $count = 1): self
    {
        $this->statistics[$type] = ($this->statistics[$type] ?? 0) + $count;

        return $this;
    }

    /**
     * Get current statistics
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * Get session duration
     */
    public function getSessionDuration(): float
    {
        return microtime(true) - $this->startTime;
    }

    /**
     * Securely validate and sanitize environment variable
     *
     * @param string $key Environment variable key
     * @param array $allowedValues Optional array of allowed values
     * @param string $default Default value if validation fails
     * @return string Sanitized value
     */
    private function secureEnv(string $key, array $allowedValues = [], string $default = ''): string
    {
        $value = env($key, $default);

        // Convert to string and trim
        $value = trim((string) $value);

        // If allowed values are specified, validate against them
        if ($allowedValues !== []) {
            return in_array($value, $allowedValues, true) ? $value : $default;
        }

        // Basic sanitization: remove potentially dangerous characters
        $value = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $value);

        // Limit length to prevent buffer overflow attacks
        return substr((string) $value, 0, 100);
    }

    /**
     * Detect if terminal supports color (secure implementation)
     */
    private function detectColorSupport(): bool
    {
        // Check for NO_COLOR environment variable (standard)
        $noColor = $this->secureEnv('NO_COLOR', ['1', 'true', 'yes', 'on'], '');
        if ($noColor !== '' && $noColor !== '0') {
            return false;
        }

        // Get and validate TERM environment variable
        $term = $this->secureEnv('TERM');
        if ($term === '' || $term === '0') {
            return false;
        }

        // Check for dumb terminals (no color support)
        $dumbTerminals = ['dumb', 'unknown', 'cons25', 'console'];
        if (in_array(strtolower($term), $dumbTerminals, true)) {
            return false;
        }

        // Check for color-capable terminals
        $colorTerminals = ['xterm', 'xterm-256color', 'screen', 'tmux', 'linux', 'cygwin'];
        $termLower = strtolower($term);

        foreach ($colorTerminals as $colorTerm) {
            if (Str::startsWith($termLower, $colorTerm)) {
                return true;
            }
        }

        // Default to false for unknown terminals (secure by default)
        return false;
    }

    /**
     * Detect if terminal supports Unicode (secure implementation)
     */
    private function detectUnicodeSupport(): bool
    {
        try {
            // Read the configured locale from the environment. This is
            // side-effect free; querying via setlocale() with an int arg is
            // a TypeError on PHP 8+.
            $locale = getenv('LC_ALL') ?: (getenv('LC_CTYPE') ?: getenv('LANG'));

            // Validate locale is not empty or null
            if (empty($locale) || ! is_string($locale)) {
                return false;
            }

            // Sanitize locale string
            $locale = trim($locale);
            if ($locale === '' || $locale === '0') {
                return false;
            }

            // Check for UTF-8 support in locale
            $localeLower = strtolower($locale);
            $utfPatterns = ['utf', 'utf8', 'utf-8'];

            foreach ($utfPatterns as $pattern) {
                if (Str::contains($localeLower, $pattern)) {
                    return true;
                }
            }

            // Additional check: Verify UTF-8 encoding capability
            if (function_exists('mb_check_encoding')) {
                $testString = '✓ Unicode Test';

                return mb_check_encoding($testString, 'UTF-8');
            }

            // Fallback: Check if we can handle basic Unicode characters
            return extension_loaded('mbstring');

        } catch (Throwable $e) {
            // Log error in development, fail safely in production
            if (app()->environment('local', 'development')) {
                error_log('Unicode detection error: ' . $e->getMessage());
            }

            return false;
        }
    }

    /**
     * Reset statistics
     */
    public function resetStatistics(): self
    {
        $this->statistics = [
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        return $this;
    }

    /**
     * Render the formatted string
     */
    public function render(): string
    {
        if ($this->message === '' || $this->message === '0') {
            return '';
        }

        // Format message as badge if enabled
        $displayMessage = $this->badgeMode
            ? $this->badgePadding . mb_strtoupper($this->message) . $this->badgePadding
            : $this->message;

        // Use predefined style tag if set and not in badge mode
        if ($this->styleTag && ! $this->badgeMode) {
            return sprintf('<%s>%s</>', $this->styleTag, $displayMessage);
        }

        // Use clickable href if set
        if ($this->isClickable && $this->href) {
            return sprintf('<href=%s>%s</>', $this->href, $displayMessage);
        }

        // Build custom formatting
        $tags = [];

        if ($this->foregroundColor) {
            $tags[] = 'fg=' . $this->foregroundColor;
        }

        if ($this->backgroundColor) {
            $tags[] = 'bg=' . $this->backgroundColor;
        }

        if ($this->textStyles !== []) {
            $tags[] = 'options=' . implode(',', $this->textStyles);
        }

        // No formatting needed
        if ($tags === []) {
            return $displayMessage;
        }

        // Build formatted string
        return sprintf('<%s>%s</>', implode(';', $tags), $displayMessage);
    }

    /**
     * Convert to string (alias for render)
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Create a badge with specified style
     *
     * @param string $text Badge text
     * @param string|null $style Badge style (BADGE_STYLE_*)
     */
    public static function badge(string $text, ?string $style = self::BADGE_STYLE_PRIMARY): string
    {
        return self::create()
            ->addMessage($text)
            ->isBadge($style)
            ->render();
    }

    /**
     * Create multiple badges in a row
     *
     * @param array $badges Array of ['text' => string, 'style' => string] or just strings
     * @param string $separator Separator between badges
     */
    public static function badges(array $badges, string $separator = ' '): string
    {
        $rendered = [];

        foreach ($badges as $badge) {
            if (is_string($badge)) {
                $rendered[] = self::badge($badge);
            } elseif (is_array($badge)) {
                $text = $badge['text'] ?? $badge[0] ?? '';
                $style = $badge['style'] ?? $badge[1] ?? self::BADGE_STYLE_PRIMARY;
                $rendered[] = self::badge($text, $style);
            }
        }

        return implode($separator, $rendered);
    }

    /**
     * Static helper for quick formatting
     *
     * @param string $message The message to format
     * @param string|null $foreground Foreground color
     * @param string|null $background Background color
     * @param array $styles Text styles
     */
    public static function format(
        string $message,
        ?string $foreground = null,
        ?string $background = null,
        array $styles = []
    ): string {
        $formatter = self::create()->addMessage($message);

        if ($foreground) {
            $formatter->addTextColor($foreground);
        }

        if ($background) {
            $formatter->addBackgroundColor($background);
        }

        if ($styles !== []) {
            $formatter->addTextStyles($styles);
        }

        return $formatter->render();
    }

    /**
     * Static helper for success messages
     */
    public static function success(string $message): string
    {
        return self::create()
            ->addMessage($message)
            ->addTextColor(self::GREEN)
            ->addTextStyles(self::BOLD)
            ->render();
    }

    /**
     * Static helper for error messages
     */
    public static function error(string $message): string
    {
        return self::create()
            ->addMessage($message)
            ->addTextColor(self::WHITE)
            ->addBackgroundColor(self::BG_RED)
            ->addTextStyles(self::BOLD)
            ->render();
    }

    /**
     * Static helper for warning messages
     */
    public static function warning(string $message): string
    {
        return self::create()
            ->addMessage($message)
            ->addTextColor(self::YELLOW)
            ->addTextStyles(self::BOLD)
            ->render();
    }

    /**
     * Static helper for info messages
     */
    public static function info(string $message): string
    {
        return self::create()
            ->addMessage($message)
            ->addTextColor(text: self::CYAN)
            ->render();
    }

    /**
     * Create a progress indicator format
     *
     * @param string $label Label text
     * @param string $status Status text (e.g., "RUNNING", "DONE")
     * @param bool $isError Whether this is an error status
     */
    public static function progress(string $label, string $status, bool $isError = false): string
    {
        $statusColor = $isError ? self::RED : self::GREEN;
        $statusStyles = $isError ? [self::BOLD, self::BLINK] : [self::BOLD];

        return sprintf(
            '%s %s',
            $label,
            self::create()
                ->addMessage($status)
                ->addTextColor($statusColor)
                ->addTextStyles($statusStyles)
                ->render()
        );
    }

    /**
     * Create a progress indicator with badge
     *
     * @param string $label Label text
     * @param string $status Status text
     * @param string $badgeStyle Badge style to use
     */
    public static function progressBadge(string $label, string $status, string $badgeStyle): string
    {
        return sprintf(
            '%s %s',
            $label,
            self::badge($status, $badgeStyle)
        );
    }

    /**
     * Create a clickable link format
     *
     * @param string $text Display text
     * @param string $url URL to link to
     */
    public static function link(string $text, string $url): string
    {
        return self::create()
            ->addMessage($text)
            ->setHref($url)
            ->render();
    }

    /**
     * Format with hex colors
     *
     * @param string $message The message
     * @param string|null $hexFg Hex foreground color (e.g., '#e74c3c')
     * @param string|null $hexBg Hex background color (e.g., '#2c3e50')
     */
    public static function hex(string $message, ?string $hexFg = null, ?string $hexBg = null): string
    {
        $formatter = self::create()->addMessage($message);

        if ($hexFg) {
            $formatter->addTextColor($hexFg);
        }

        if ($hexBg) {
            $formatter->addBackgroundColor($hexBg);
        }

        return $formatter->render();
    }

    /**
     * Create a status line with label and badge
     *
     * @param string $label Label text
     * @param string $badgeText Badge text
     * @param string $badgeStyle Badge style
     */
    public static function statusLineWithBadge(string $label, string $badgeText, string $badgeStyle): string
    {
        return sprintf(
            '%s %s',
            str_pad($label, 30),
            self::badge($badgeText, $badgeStyle)
        );
    }

    /**
     * Get all available badge styles
     */
    public static function getBadgeStyles(): array
    {
        return [
            self::BADGE_STYLE_PRIMARY,
            self::BADGE_STYLE_SECONDARY,
            self::BADGE_STYLE_SUCCESS,
            self::BADGE_STYLE_DANGER,
            self::BADGE_STYLE_WARNING,
            self::BADGE_STYLE_INFO,
            self::BADGE_STYLE_LIGHT,
            self::BADGE_STYLE_DARK,
        ];
    }

    /**
     * Create a status line with tree structure (generic)
     *
     * @param string $name The item name
     * @param string $status The status (RUNNING, DONE, FAILED, SKIPPED, etc.)
     * @param string $duration Duration in milliseconds
     * @param bool $isLast Whether this is the last item in the tree
     * @param string $reason Optional reason for status
     */
    public static function statusLine(
        string $name,
        string $status,
        string $duration = '',
        bool $isLast = false,
        string $reason = ''
    ): string {
        $formatter = self::create();

        $treeSymbol = $isLast ? $formatter->getTreeSymbol('tree_last') : $formatter->getTreeSymbol('tree_mid');

        $statusSymbol = match (strtoupper($status)) {
            'RUNNING' => $formatter->getTreeSymbol('running'),
            'DONE', 'SUCCESS', 'COMPLETED' => $formatter->getTreeSymbol('success'),
            'FAILED', 'ERROR' => $formatter->getTreeSymbol('error'),
            'SKIPPED' => $formatter->getTreeSymbol('skipped'),
            'WARNING' => $formatter->getTreeSymbol('warning'),
            default => $formatter->getTreeSymbol('info'),
        };

        $statusColor = match (strtoupper($status)) {
            'RUNNING' => self::YELLOW,
            'DONE', 'SUCCESS', 'COMPLETED' => self::GREEN,
            'FAILED', 'ERROR' => self::RED,
            'SKIPPED' => self::YELLOW,
            'WARNING' => self::YELLOW,
            default => self::GRAY,
        };

        $line = $treeSymbol . ' ' . $statusSymbol . ' ' . $name;

        if ($duration !== '' && $duration !== '0') {
            $line .= ' ' . $duration . 'ms';
        }

        $line .= ' ' . $formatter->colorize(strtoupper($status), $statusColor, true);

        if ($reason !== '' && $reason !== '0') {
            $line .= ' ' . $formatter->colorize("({$reason})", self::GRAY);
        }

        return $line;
    }

    /**
     * Create a header with tree structure (generic)
     *
     * @param string $title The header title
     * @param int $count Number of items
     * @param string $itemLabel Label for the count (e.g., "items", "files")
     * @param bool $isLast Whether this is the last header
     */
    public static function header(string $title, int $count, string $itemLabel = 'items', bool $isLast = false): string
    {
        $formatter = self::create();

        $headerSymbol = $formatter->getTreeSymbol('package');
        $titleDisplay = $formatter->colorize("{$headerSymbol} {$title}", self::CYAN, true);
        $countDisplay = $formatter->colorize("({$count} {$itemLabel})", self::GRAY);

        return $titleDisplay . ' ' . $countDisplay;
    }

    /**
     * Create a statistics line (generic)
     *
     * @param string $label The label text
     * @param int $count The count value
     * @param string $symbol The symbol to use
     * @param string $color The color for the count
     */
    public static function statisticsLine(string $label, int $count, string $symbol, string $color): string
    {
        $formatter = self::create();

        $symbolDisplay = $formatter->colorize($formatter->getTreeSymbol($symbol), $color);
        $countDisplay = $formatter->colorize((string) $count, $color, true);

        return "  {$symbolDisplay} {$label}: {$countDisplay}";
    }

    /**
     * Format runtime with adaptive units and optional coloring
     *
     * @param float $ms Milliseconds
     * @param bool $colored Whether to apply performance-based coloring
     */
    public static function formatRuntime(float $ms, bool $colored = false): string
    {
        $formatter = self::create();

        // Determine unit
        $formatted = match (true) {
            $ms < 1000 => number_format($ms, 2) . ' ms',
            $ms < 60000 => number_format($ms / 1000, 2) . ' s',
            default => number_format($ms / 60000, 2) . ' min'
        };

        // Apply color in advanced mode if requested
        if ($colored) {
            $color = self::getPerformanceColor($ms);

            return $formatter->colorize($formatted, $color);
        }

        return $formatter->colorize($formatted, 'gray');
    }

    /**
     * Get performance-based color for runtime
     *
     * @param float $ms Milliseconds
     */
    public static function getPerformanceColor(float $ms): string
    {
        return match (true) {
            $ms < 100 => self::GREEN,
            $ms < 500 => self::GRAY,
            $ms < 1000 => self::YELLOW,
            default => self::RED
        };
    }

    /**
     * Format class name based on verbosity
     *
     * @param string $className Full class name
     * @param bool $verbose Whether to show full namespace
     */
    public static function formatClassName(string $className, bool $verbose = false): string
    {
        $formatter = self::create();
        $shortName = self::getShortClassName($className);

        // In verbose mode, show full namespace
        if ($verbose && str_contains($className, '\\')) {
            return sprintf('%s %s', $shortName, $formatter->colorize("({$className})", 'gray'));
        }

        return $shortName;
    }

    /**
     * Get short class name from FQCN
     *
     * @param string $className Full class name
     */
    public static function getShortClassName(string $className): string
    {
        $parts = Str::of($className)->explode('\\');

        return $parts->last() ?: $className;
    }

    /**
     * Display execution summary with statistics (generic)
     *
     * @param array $stats Statistics array
     * @param string $title Optional custom title
     */
    public static function displaySummary(array $stats, string $title = 'EXECUTION SUMMARY'): string
    {
        $formatter = self::create();
        $output = [];

        // Separator
        $output[] = $formatter->colorize(Str::repeat('─', 60), self::GRAY);

        // Header
        $output[] = $formatter->colorize($title, self::BRIGHT_CYAN, true);
        $output[] = '';

        // Statistics
        $output[] = $formatter->colorize('📊 Execution Statistics:', self::WHITE, true);
        $output[] = self::displayStatisticsTable([
            ['Total', (string) $stats['total'], self::BADGE_STYLE_INFO],
            ['Successful', (string) $stats['success'], self::BADGE_STYLE_SUCCESS],
            ['Failed', (string) $stats['failed'], $stats['failed'] > 0 ? self::BADGE_STYLE_DANGER : self::BADGE_STYLE_SECONDARY],
        ]);
        $output[] = '';

        // Performance Metrics
        $output[] = $formatter->colorize('⚡ Performance Metrics:', self::WHITE, true);
        $output[] = self::displayPerformanceMetrics($stats);
        $output[] = '';

        // Error details (if any)
        if (! empty($stats['errors'])) {
            $output[] = $formatter->colorize('❌ Failed Items:', self::RED, true);
            $output[] = self::displayErrorDetails($stats['errors']);
            $output[] = '';
        }

        // Final status badges
        $output[] = self::getExecutionStatusBadges($stats);

        return implode("\n", $output);
    }

    /**
     * Display statistics table
     *
     * @param array $items Array of [label, value, badgeStyle]
     */
    public static function displayStatisticsTable(array $items): string
    {
        $output = [];

        foreach ($items as [$label, $value, $style]) {
            $output[] = sprintf('   %s %s', Str::padRight($label . ':', 16), self::badge($value, $style));
        }

        return implode("\n", $output);
    }

    /**
     * Display performance metrics
     *
     * @param array $stats Statistics array
     */
    public static function displayPerformanceMetrics(array $stats): string
    {
        $formatter = self::create();
        $output = [];

        // Total time
        $totalColor = match (true) {
            $stats['totalTime'] < 1000 => self::GREEN,
            $stats['totalTime'] < 5000 => self::YELLOW,
            default => self::RED
        };

        $output[] = sprintf(
            '   %s %s',
            Str::padRight('Total Time:', 16),
            $formatter->colorize(self::formatRuntime($stats['totalTime'], false), $totalColor, true)
        );

        // Average time
        $avgTime = $stats['totalTime'] / $stats['total'];
        $output[] = sprintf(
            '   %s %s',
            Str::padRight('Average Time:', 16),
            self::formatRuntime($avgTime, true)
        );

        // Fastest/Slowest (only if more than 1 item)
        if ($stats['total'] > 1 && $stats['fastest']['class']) {
            $output[] = sprintf(
                '   %s %s %s',
                Str::padRight('Fastest:', 16),
                $formatter->colorize($stats['fastest']['class'], 'green'),
                $formatter->colorize('(' . self::formatRuntime($stats['fastest']['time'], false) . ')', 'gray')
            );

            $output[] = sprintf(
                '   %s %s %s',
                Str::padRight('Slowest:', 16),
                $formatter->colorize($stats['slowest']['class'], 'yellow'),
                $formatter->colorize('(' . self::formatRuntime($stats['slowest']['time'], false) . ')', 'gray')
            );
        }

        // Success rate
        $successRate = ($stats['success'] / $stats['total']) * 100;
        $rateColor = match (true) {
            $successRate >= 100 => self::GREEN,
            $successRate >= 80 => self::YELLOW,
            default => self::RED
        };

        $output[] = sprintf(
            '   %s %s',
            Str::padRight('Success Rate:', 16),
            $formatter->colorize(number_format($successRate, 1) . '%', $rateColor, true)
        );

        return implode("\n", $output);
    }

    /**
     * Display error details
     *
     * @param array $errors Array of error details
     */
    public static function displayErrorDetails(array $errors): string
    {
        $formatter = self::create();
        $output = [];

        foreach ($errors as $index => $error) {
            $output[] = sprintf('   %d. %s', $index + 1, $formatter->colorize($error['class'], 'yellow'));

            $message = Str::length($error['message']) > 80
                ? Str::substr($error['message'], 0, 77) . '...'
                : $error['message'];

            $output[] = sprintf(
                '      %s %s',
                self::badge(self::getShortClassName($error['type']), self::BADGE_STYLE_DARK),
                $formatter->colorize($message, 'gray')
            );
        }

        return implode("\n", $output);
    }

    /**
     * Get execution status badges
     *
     * @param array $stats Statistics array
     */
    public static function getExecutionStatusBadges(array $stats): string
    {
        $badges = match (true) {
            $stats['failed'] === 0 => [['✓ ALL COMPLETED', self::BADGE_STYLE_SUCCESS]],
            $stats['success'] > 0 => [
                ['⚠ COMPLETED WITH ERRORS', self::BADGE_STYLE_WARNING],
                [$stats['failed'] . ' FAILED', self::BADGE_STYLE_DANGER],
            ],
            default => [['✗ ALL FAILED', self::BADGE_STYLE_DANGER]]
        };

        return self::badges($badges);
    }
}
