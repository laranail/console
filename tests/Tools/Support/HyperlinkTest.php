<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\Hyperlink;

final class HyperlinkTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
    }

    public function test_allow_list(): void
    {
        self::assertTrue(Hyperlink::isAllowed('https://example.com'));
        self::assertTrue(Hyperlink::isAllowed('mailto:a@b.com'));
        self::assertFalse(Hyperlink::isAllowed('javascript:alert(1)'));
        self::assertFalse(Hyperlink::isAllowed('file:///etc/passwd'));
    }

    public function test_sanitize_strips_control_and_separator(): void
    {
        self::assertSame('https://x.comY', Hyperlink::sanitize("https://x.com\x07;Y"));
    }

    public function test_render_emits_osc8_in_a_tty(): void
    {
        Capabilities::fake(interactive: true);

        $out = Hyperlink::render('docs', 'https://example.com');
        self::assertStringContainsString("\e]8;;https://example.com\e\\docs\e]8;;\e\\", $out);
    }

    public function test_render_falls_back_to_plain_when_not_interactive(): void
    {
        Capabilities::fake(interactive: false);

        self::assertSame('docs (https://example.com)', Hyperlink::render('docs', 'https://example.com'));
    }

    public function test_render_unsafe_url_returns_label_only(): void
    {
        Capabilities::fake(interactive: true);

        self::assertSame('click', Hyperlink::render('click', 'javascript:alert(1)'));
    }
}
