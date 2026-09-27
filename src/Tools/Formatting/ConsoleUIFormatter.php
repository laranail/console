<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Formatting;

use Stringable;
use BadMethodCallException;
use InvalidArgumentException;
use Simtabi\Laranail\Console\Tools\Support\Color;
use Simtabi\Laranail\Console\Tools\Support\Emoji;
use Simtabi\Laranail\Console\Tools\Support\Hyperlink;
use Symfony\Component\Console\Output\OutputInterface;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\DisplayWidth;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Simtabi\Laranail\Console\Tools\Exceptions\InvalidColorException;

/**
 * Fluent builder for one styled string of Symfony Console markup
 * (`<fg=cyan;bg=white;options=bold>message</>`), with badge and link support.
 *
 * The builder is `Stringable`: pass it where `string|Stringable` is accepted, cast
 * it with `(string)` where a plain `string` is declared (under `strict_types`,
 * Symfony's `writeln()` rejects the object), or call `write($output)` directly.
 *
 * @example
 * // Fluent
 * $text = ConsoleUIFormatter::create()
 *     ->txtColorRed()->bgColorWhite()->bold()
 *     ->icon('rocket')              // 🚀 (or its ASCII fallback), via Support\Emoji
 *     ->message('Deployed :tada:')  // shortcodes resolve the same way
 *     ->padding(2)                  // two spaces either side, inside the background
 *     ->lineHeight(3)               // one blank background line above and one below
 *     ->addSpaceBefore(2)           // outside the background: indent by two spaces
 *     ->addSpaceAfter(1, ConsoleUIFormatter::TAB);
 * $text->write($output);   // or $output->writeln((string) $text)
 *
 * // Badge
 * $badge = ConsoleUIFormatter::create()
 *     ->addMessage('NEW')
 *     ->isBadge(ConsoleUIFormatter::BADGE_STYLE_SUCCESS)
 *     ->render();
 *
 * Emoji: `message()` and `icon()` resolve `:shortcodes:` through {@see Emoji}, which
 * uses the full `laranail/emojis` catalogue when that optional package is installed
 * and degrades to ASCII on a terminal without Unicode. `addMessage()` keeps the text
 * exactly as given.
 *
 * Colours: Symfony's own names (`red`, `bright-red`, `default`, …) pass through
 * unchanged; anything else {@see Color::parse()} accepts (`orange`, `#7c3aed`,
 * `rgb(…)`, `hsl(…)`, `@196`) is converted to hex. An unknown colour throws
 * {@see InvalidColorException} when it is set, not when the string is written.
 *
 * @method self txtColorBlack()
 * @method self txtColorRed()
 * @method self txtColorGreen()
 * @method self txtColorYellow()
 * @method self txtColorBlue()
 * @method self txtColorMagenta()
 * @method self txtColorCyan()
 * @method self txtColorWhite()
 * @method self txtColorDefault()
 * @method self txtColorGray()
 * @method self txtColorBrightRed()
 * @method self txtColorBrightGreen()
 * @method self txtColorBrightYellow()
 * @method self txtColorBrightBlue()
 * @method self txtColorBrightMagenta()
 * @method self txtColorBrightCyan()
 * @method self txtColorBrightWhite()
 * @method self txtColorLime()
 * @method self txtColorGrey()
 * @method self txtColorSilver()
 * @method self txtColorMaroon()
 * @method self txtColorOlive()
 * @method self txtColorNavy()
 * @method self txtColorTeal()
 * @method self txtColorPurple()
 * @method self txtColorOrange()
 * @method self txtColorPink()
 * @method self txtColorBrown()
 * @method self txtColorGold()
 * @method self txtColorSlate()
 * @method self txtColorIndigo()
 * @method self txtColorViolet()
 * @method self txtColorCrimson()
 * @method self txtColorCoral()
 * @method self txtColorSalmon()
 * @method self txtColorTurquoise()
 * @method self txtColorAqua()
 * @method self txtColorMint()
 * @method self bgColorBlack()
 * @method self bgColorRed()
 * @method self bgColorGreen()
 * @method self bgColorYellow()
 * @method self bgColorBlue()
 * @method self bgColorMagenta()
 * @method self bgColorCyan()
 * @method self bgColorWhite()
 * @method self bgColorDefault()
 * @method self bgColorGray()
 * @method self bgColorBrightRed()
 * @method self bgColorBrightGreen()
 * @method self bgColorBrightYellow()
 * @method self bgColorBrightBlue()
 * @method self bgColorBrightMagenta()
 * @method self bgColorBrightCyan()
 * @method self bgColorBrightWhite()
 * @method self bgColorLime()
 * @method self bgColorGrey()
 * @method self bgColorSilver()
 * @method self bgColorMaroon()
 * @method self bgColorOlive()
 * @method self bgColorNavy()
 * @method self bgColorTeal()
 * @method self bgColorPurple()
 * @method self bgColorOrange()
 * @method self bgColorPink()
 * @method self bgColorBrown()
 * @method self bgColorGold()
 * @method self bgColorSlate()
 * @method self bgColorIndigo()
 * @method self bgColorViolet()
 * @method self bgColorCrimson()
 * @method self bgColorCoral()
 * @method self bgColorSalmon()
 * @method self bgColorTurquoise()
 * @method self bgColorAqua()
 * @method self bgColorMint()
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

    // ANSI Color Codes (for terminal compatibility)
    public const array ANSI_COLORS = [
        // Foreground colors
        'black'          => "\033[30m",
        'red'            => "\033[31m",
        'green'          => "\033[32m",
        'yellow'         => "\033[33m",
        'blue'           => "\033[34m",
        'magenta'        => "\033[35m",
        'cyan'           => "\033[36m",
        'white'          => "\033[37m",
        'gray'           => "\033[90m",
        'bright_red'     => "\033[91m",
        'bright_green'   => "\033[92m",
        'bright_yellow'  => "\033[93m",
        'bright_blue'    => "\033[94m",
        'bright_magenta' => "\033[95m",
        'bright_cyan'    => "\033[96m",
        'bright_white'   => "\033[97m",

        // Background colors
        'black_bg'          => "\033[40m",
        'red_bg'            => "\033[41m",
        'green_bg'          => "\033[42m",
        'yellow_bg'         => "\033[43m",
        'blue_bg'           => "\033[44m",
        'magenta_bg'        => "\033[45m",
        'cyan_bg'           => "\033[46m",
        'white_bg'          => "\033[47m",
        'gray_bg'           => "\033[100m",
        'bright_red_bg'     => "\033[101m",
        'bright_green_bg'   => "\033[102m",
        'bright_yellow_bg'  => "\033[103m",
        'bright_blue_bg'    => "\033[104m",
        'bright_magenta_bg' => "\033[105m",
        'bright_cyan_bg'    => "\033[106m",
        'bright_white_bg'   => "\033[107m",

        // Text styles
        'bold'      => "\033[1m",
        'dim'       => "\033[2m",
        'italic'    => "\033[3m",
        'underline' => "\033[4m",
        'reset'     => "\033[0m",
    ];

    /**
     * Colour names Symfony's formatter understands natively. These pass through
     * as names, so they keep following the terminal's own palette; every other
     * colour is converted to hex through {@see Color::parse()}.
     */
    public const array SYMFONY_COLORS = [
        'black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'default',
        'gray', 'bright-red', 'bright-green', 'bright-yellow', 'bright-blue',
        'bright-magenta', 'bright-cyan', 'bright-white',
    ];

    /**
     * The options Symfony's formatter accepts. Anything else makes it throw at
     * write time, far from the call that set it, so they are checked when set.
     * `underline` and `hidden` are accepted as aliases, matching the constants.
     */
    public const array TEXT_OPTIONS = ['bold', 'underscore', 'blink', 'reverse', 'conceal'];

    /** Whitespace {@see addSpaceBefore()} and {@see addSpaceAfter()} accept. */
    public const string SPACE = ' ';

    public const string TAB = "\t";

    private const array OPTION_ALIASES = ['underline' => 'underscore', 'hidden' => 'conceal'];

    // Badge color schemes
    private const array BADGE_SCHEMES = [
        self::BADGE_STYLE_PRIMARY => [
            'fg'     => self::WHITE,
            'bg'     => '#0d6efd', // Bootstrap primary blue
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_SECONDARY => [
            'fg'     => self::WHITE,
            'bg'     => '#6c757d', // Bootstrap secondary gray
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_SUCCESS => [
            'fg'     => self::WHITE,
            'bg'     => '#198754', // Bootstrap success green
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_DANGER => [
            'fg'     => self::WHITE,
            'bg'     => '#dc3545', // Bootstrap danger red
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_WARNING => [
            'fg'     => self::BLACK,
            'bg'     => '#ffc107', // Bootstrap warning yellow
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_INFO => [
            'fg'     => self::BLACK,
            'bg'     => '#0dcaf0', // Bootstrap info cyan
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_LIGHT => [
            'fg'     => self::BLACK,
            'bg'     => '#f8f9fa', // Bootstrap light gray
            'styles' => [self::BOLD],
        ],
        self::BADGE_STYLE_DARK => [
            'fg'     => self::WHITE,
            'bg'     => '#212529', // Bootstrap dark
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

    private int $padding = 0;

    private int $lineHeight = 1;

    private bool $allowMarkup = false;

    private string $spaceBefore = '';

    private string $spaceAfter = '';

    private ?Capabilities $capabilities = null;

    private string $icon = '';

    private bool $resolveShortcodes = false;

    // Terminal capability detection
    private readonly bool $supportsColor;

    /**
     * Private constructor to enforce factory method usage
     */
    private function __construct()
    {
        $this->supportsColor = $this->detectColorSupport();
    }

    /**
     * Convert to string (alias for render)
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * `txtColor<Name>()` and `bgColor<Name>()` for every name in the class
     * docblock: `txtColorBrightRed()` sets `bright-red`, `bgColorOrange()` sets
     * the hex `Color` knows as orange.
     *
     * @param array<int, mixed> $arguments
     *
     * @throws InvalidColorException for an unknown colour name
     */
    public function __call(string $method, array $arguments): self
    {
        foreach (['txtColor' => false, 'bgColor' => true] as $prefix => $background) {
            if (str_starts_with($method, $prefix) && strlen($method) > strlen($prefix)) {
                // StudlyCase to kebab: BrightRed -> bright-red
                $name = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', substr($method, strlen($prefix))));

                return $background ? $this->bg($name) : $this->fg($name);
            }
        }

        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', self::class, $method));
    }

    public static function create(): self
    {
        return new self;
    }

    /**
     * Strip terminal control characters (C0 controls, ESC, DEL) from a string,
     * preserving tab and newline. Prevents ANSI/CR output-spoofing injection
     * when rendering user-controlled text.
     */
    public static function sanitizeText(string $text): string
    {
        return (string) preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/', '', $text);
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
        array $styles = [],
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
     * Normalise a colour to what Symfony's formatter accepts: one of its own
     * names, or a `#rrggbb` hex.
     *
     * @throws InvalidColorException
     */
    public static function resolveColor(string $color): string
    {
        $lower = strtolower(trim($color));

        if (in_array($lower, self::SYMFONY_COLORS, true)) {
            return $lower;
        }

        return Color::parseStrict($color);
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
     * Set the message content
     */
    public function addMessage(string $message): self
    {
        $this->message = self::sanitizeText($message);
        $this->resolveShortcodes = false;

        return $this;
    }

    /**
     * Set the message: text, emoji, icons, or several lines. Formatter tags in
     * it are shown literally unless {@see markup()} is on.
     */
    public function message(string|Stringable $message): self
    {
        $this->addMessage((string) $message);

        // Resolved at render time, so a capabilities() call later in the chain still applies.
        $this->resolveShortcodes = true;

        return $this;
    }

    /**
     * Put an emoji in front of the message, by name (`rocket`, `check`, `tada`),
     * with one space between. Call it before or after {@see message()}; an
     * unknown name adds nothing.
     */
    public function icon(string $name): self
    {
        // Stored by name and resolved at render time, like message() shortcodes.
        $this->icon = $name;

        return $this;
    }

    /**
     * Use these capabilities for emoji fallback and {@see toAnsi()}, instead of
     * detecting the terminal.
     */
    public function capabilities(Capabilities $capabilities): self
    {
        $this->capabilities = $capabilities;

        return $this;
    }

    /**
     * Whitespace before the message, outside its background. Calls add up, so
     * `addSpaceBefore()->addSpaceBefore(1, self::TAB)` is a space then a tab.
     *
     * @param string $with {@see SPACE} or {@see TAB}
     */
    public function addSpaceBefore(int $count = 1, string $with = self::SPACE): self
    {
        $this->spaceBefore .= $this->whitespace($count, $with);

        return $this;
    }

    /**
     * Whitespace after the message, outside its background. Calls add up.
     *
     * @param string $with {@see SPACE} or {@see TAB}
     */
    public function addSpaceAfter(int $count = 1, string $with = self::SPACE): self
    {
        $this->spaceAfter .= $this->whitespace($count, $with);

        return $this;
    }

    /**
     * Treat formatter tags inside the message as markup instead of text. Off by
     * default, so a message carrying `</>` or `<fg=…>` cannot restyle the output.
     */
    public function markup(bool $allow = true): self
    {
        $this->allowMarkup = $allow;

        return $this;
    }

    /**
     * Foreground colour: a Symfony name, a {@see Color} name, hex, rgb(), hsl() or @N.
     *
     * @throws InvalidColorException
     */
    public function fg(string $color): self
    {
        return $this->addTextColor($color);
    }

    /**
     * Background colour, accepting the same forms as {@see fg()}.
     *
     * @throws InvalidColorException
     */
    public function bg(string $color): self
    {
        return $this->addBackgroundColor($color);
    }

    public function bold(): self
    {
        return $this->addTextStyles(self::BOLD);
    }

    public function underline(): self
    {
        return $this->addTextStyles(self::UNDERSCORE);
    }

    public function blink(): self
    {
        return $this->addTextStyles(self::BLINK);
    }

    public function reverse(): self
    {
        return $this->addTextStyles(self::REVERSE);
    }

    public function conceal(): self
    {
        return $this->addTextStyles(self::CONCEAL);
    }

    public function href(string $url): self
    {
        return $this->setHref($url);
    }

    /**
     * Spaces either side of the message, inside its background.
     */
    public function padding(int $spaces): self
    {
        if ($spaces < 0) {
            throw new InvalidArgumentException("Padding cannot be negative, got {$spaces}.");
        }

        $this->padding = $spaces;

        return $this;
    }

    /**
     * Total height in lines. The message sits in the middle, and the extra lines
     * are blank lines carrying the background, so `lineHeight(3)` puts one line
     * above and one below. Terminals have no line spacing; this is the nearest
     * thing, and it only shows when a background is set.
     */
    public function lineHeight(int $lines): self
    {
        if ($lines < 1) {
            throw new InvalidArgumentException("Line height must be at least 1, got {$lines}.");
        }

        $this->lineHeight = $lines;

        return $this;
    }

    /**
     * Render and write to an output, one call per line.
     */
    public function write(OutputInterface $output): void
    {
        $output->writeln($this->render());
    }

    /**
     * The rendered string as raw ANSI, ready to echo. Plain text when the
     * terminal has no colour support.
     */
    public function toAnsi(?Capabilities $capabilities = null): string
    {
        $decorated = ($capabilities ?? $this->capabilities ?? Capabilities::detect())->supportsColor();

        return new OutputFormatter($decorated)->format($this->render()) ?? '';
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
        ?string $href = null,
    ): self {
        $this->foregroundColor = self::resolveColor($text);
        $this->styleTag = $styleTag;
        $this->isClickable = false;
        $this->href = null;

        // Route the href through the same sanitisation + scheme allow-list as
        // setHref(), so a hostile URL can't emit an arbitrary OSC-8 hyperlink.
        if ($isClickable && $href !== null && $href !== '') {
            $this->setHref($href);
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
        $this->backgroundColor = self::resolveColor($text);

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

        foreach ($styles as $i => $style) {
            $style = self::OPTION_ALIASES[$style] ?? $style;

            if (! in_array($style, self::TEXT_OPTIONS, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid text style "%s". Expected one of: %s.',
                    $style,
                    implode(', ', [...self::TEXT_OPTIONS, ...array_keys(self::OPTION_ALIASES)]),
                ));
            }

            $styles[$i] = $style;
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
        // Delegate the allow-list + sanitisation to Support\Hyperlink (the single
        // source of truth). A rejected URL degrades to plain, non-clickable text
        // rather than emitting an attacker-controlled hyperlink.
        $safe = Hyperlink::safe($url);

        $this->href = $safe;
        $this->isClickable = $safe !== null;

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
        $this->padding = 0;
        $this->lineHeight = 1;
        $this->allowMarkup = false;
        $this->spaceBefore = '';
        $this->spaceAfter = '';
        $this->icon = '';
        $this->resolveShortcodes = false;

        return $this;
    }

    /**
     * Colorize text with ANSI codes (for terminal compatibility)
     */
    public function colorize(string $text, string $color, bool $bold = false, ?string $background = null): string
    {
        if (! $this->supportsColor) {
            return $text;
        }

        // The colour constants spell bright colours `bright-red`; ANSI_COLORS keys them `bright_red`.
        $color = str_replace('-', '_', $color);
        $background = $background !== null ? str_replace('-', '_', $background) : null;
        $colorCode = self::ANSI_COLORS[$color] ?? '';
        $boldCode = $bold ? self::ANSI_COLORS['bold'] : '';
        // Background colour tokens (e.g. BG_RED = 'red') map to the '*_bg' ANSI key.
        $backgroundCode = $background !== null && $background !== ''
            ? (self::ANSI_COLORS[$background . '_bg'] ?? self::ANSI_COLORS[$background] ?? '')
            : '';
        $resetCode = self::ANSI_COLORS['reset'];
        $text = self::sanitizeText($text);

        return "{$boldCode}{$backgroundCode}{$colorCode}{$text}{$resetCode}";
    }

    /**
     * Render the formatted string: Symfony markup, one tag per line.
     */
    public function render(): string
    {
        if ($this->message === '') {
            return '';
        }

        $emoji = $this->emoji();
        $message = $this->resolveShortcodes ? self::sanitizeText($emoji->render($this->message)) : $this->message;
        $message = $this->badgeMode ? mb_strtoupper($message) : $message;
        $icon = $this->icon === '' ? '' : self::sanitizeText($emoji->get($this->icon));
        $lines = explode("\n", $icon === '' ? $message : $icon . ' ' . $message);
        $width = max(array_map($this->visibleWidth(...), $lines));
        $pad = ($this->badgeMode ? $this->badgePadding : '') . str_repeat(' ', $this->padding);

        $rendered = [];

        foreach ($lines as $line) {
            $fill = str_repeat(' ', $width - $this->visibleWidth($line));
            $text = $this->allowMarkup ? $line : OutputFormatter::escape($line);
            $rendered[] = $this->spaceBefore . $this->wrap($pad . $text . $fill . $pad, link: true) . $this->spaceAfter;
        }

        // Extra height is blank lines carrying the background, split around the message.
        $blank = $this->spaceBefore . $this->wrap(str_repeat(' ', $width + 2 * DisplayWidth::of($pad)), link: false) . $this->spaceAfter;
        $above = intdiv($this->lineHeight - 1, 2);
        $below = $this->lineHeight - 1 - $above;

        return implode("\n", [
            ...array_fill(0, $above, $blank),
            ...$rendered,
            ...array_fill(0, $below, $blank),
        ]);
    }

    /**
     * Build the whitespace for addSpaceBefore()/addSpaceAfter().
     */
    private function whitespace(int $count, string $with): string
    {
        if ($count < 1) {
            throw new InvalidArgumentException("Space count must be positive, got {$count}.");
        }

        if ($with !== self::SPACE && $with !== self::TAB) {
            throw new InvalidArgumentException('Space must be ConsoleUIFormatter::SPACE or ConsoleUIFormatter::TAB.');
        }

        return str_repeat($with, $count);
    }

    private function emoji(): Emoji
    {
        return Emoji::make($this->capabilities);
    }

    /**
     * Wrap one line in the configured tag. `$link` is false for the blank
     * lineHeight rows, which carry the background but are not clickable.
     */
    private function wrap(string $line, bool $link): string
    {
        // A predefined style tag wins over custom colours, except in badge mode.
        if ($this->styleTag && ! $this->badgeMode) {
            return sprintf('<%s>%s</>', $this->styleTag, $line);
        }

        $tags = [];

        if ($this->foregroundColor) {
            $tags[] = 'fg=' . $this->sanitizeColorToken($this->foregroundColor);
        }

        if ($this->backgroundColor) {
            $tags[] = 'bg=' . $this->sanitizeColorToken($this->backgroundColor);
        }

        if ($this->textStyles !== []) {
            $tags[] = 'options=' . implode(',', $this->textStyles);
        }

        // Escape the URL so it can't inject formatter tags (e.g. a URL containing
        // `<fg=red>`); Hyperlink vets the scheme, but `<`/`>` must still be
        // neutralised for the Symfony tag syntax. (Hyperlink already strips `;`,
        // which would otherwise end the attribute.)
        if ($link && $this->isClickable && $this->href) {
            $tags[] = 'href=' . OutputFormatter::escape($this->href);
        }

        return $tags === [] ? $line : sprintf('<%s>%s</>', implode(';', $tags), $line);
    }

    /**
     * Columns a line occupies once written: emoji and wide glyphs count as two,
     * and markup tags (when markup is allowed) count as nothing.
     */
    private function visibleWidth(string $line): int
    {
        if ($this->allowMarkup) {
            $line = new OutputFormatter(false)->format($line) ?? '';
        }

        return DisplayWidth::of($line);
    }

    /**
     * Constrain a colour token to safe characters so it cannot break out of or
     * inject extra attributes into a `<fg=...>` / `<bg=...>` formatter tag.
     */
    private function sanitizeColorToken(string $color): string
    {
        return (string) preg_replace('/[^A-Za-z0-9#-]/', '', $color);
    }

    /**
     * Detect colour support via the shared Capabilities detector, so the whole
     * package degrades by one consistent set of rules (NO_COLOR/FORCE_COLOR/
     * TERM/TTY).
     */
    private function detectColorSupport(): bool
    {
        return Capabilities::detect()->supportsColor();
    }
}
