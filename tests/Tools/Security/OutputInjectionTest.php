<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Security;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Notifications\ConsoleChannel;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\BufferedOutput;

final class OutputInjectionTest extends TestCase
{
    /**
     * D1: terminal control characters are stripped from rendered text.
     */
    public function test_sanitize_strips_control_characters(): void
    {
        $clean = ConsoleUIFormatter::sanitizeText("a\x1b[2Jb\rc\x07d");

        self::assertStringNotContainsString("\x1b", $clean);
        self::assertStringNotContainsString("\r", $clean);
        self::assertStringNotContainsString("\x07", $clean);
        self::assertSame('a[2Jbcd', $clean);
    }

    public function test_sanitize_keeps_newlines_and_tabs(): void
    {
        self::assertSame("a\n\tb", ConsoleUIFormatter::sanitizeText("a\n\tb"));
    }

    /**
     * D2: only allow-listed URL schemes become hyperlinks; others degrade to plain text.
     */
    public function test_link_rejects_disallowed_schemes(): void
    {
        self::assertStringNotContainsString('javascript', ConsoleUIFormatter::link('x', 'javascript:alert(1)'));
        self::assertStringNotContainsString('href', ConsoleUIFormatter::link('x', 'file:///etc/passwd'));
        self::assertStringContainsString('example.com', ConsoleUIFormatter::link('site', 'https://example.com'));
    }

    /**
     * A URL containing formatter-tag characters can't inject active markup through
     * the clickable-link path — the href is escaped before interpolation.
     */
    public function test_link_url_cannot_inject_formatter_tags(): void
    {
        // an allow-listed scheme but with a smuggled formatter tag in the path
        $rendered = ConsoleUIFormatter::link('label', 'https://example.com/<fg=red>x');

        // the '<' is escaped to '\<', so it can't open an active formatter tag
        self::assertStringContainsString('\\<fg=red', $rendered);
        // and rendering it through a real formatter applies no colour to the label
        $formatted = new OutputFormatter(true)->format($rendered);
        self::assertStringNotContainsString("\033[31m", $formatted); // no red SGR injected
    }

    /**
     * D3: ConsoleChannel pins the notification level to a safe set and escapes content.
     */
    public function test_channel_pins_level_and_escapes_content(): void
    {
        $out = new BufferedOutput;
        new ConsoleChannel(['show_data' => false], $out)->send('hello', ['level' => 'fg=red;href=http://evil']);

        $written = $out->fetch();

        // The malicious level is never used as an active formatter tag.
        self::assertStringContainsString('hello', $written);
        self::assertStringNotContainsString('fg=red', $written);
        self::assertStringNotContainsString('href', $written);
    }

    public function test_channel_strips_control_chars_from_message(): void
    {
        $out = new BufferedOutput;
        new ConsoleChannel([], $out)->send("danger\x1b[2J", []);

        self::assertStringNotContainsString("\x1b", $out->fetch());
    }
}
