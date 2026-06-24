<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Support;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Enums\ControlChars;
use Simtabi\Laranail\Console\Tools\Support\Keypress;
use Simtabi\Laranail\Console\Tools\Support\Terminal;
use Symfony\Component\Console\Output\BufferedOutput;

final class KeypressTerminalTest extends TestCase
{
    public function test_translate_key_maps_sequences(): void
    {
        self::assertSame(Keypress::KEY_UP, Keypress::translateKey("\033[A"));
        self::assertSame(Keypress::KEY_DOWN, Keypress::translateKey("\033[B"));
        self::assertSame(Keypress::KEY_ENTER, Keypress::translateKey("\n"));
        self::assertSame(Keypress::KEY_SPACE, Keypress::translateKey(' '));
        self::assertSame(Keypress::KEY_ESC, Keypress::translateKey("\e"));
        self::assertSame('x', Keypress::translateKey('x'), 'plain chars pass through');
    }

    public function test_key_name_and_alt_detection(): void
    {
        self::assertSame('UP ARROW', Keypress::getKeyName(Keypress::KEY_UP));
        self::assertSame("'a'", Keypress::getKeyName('a'));
        self::assertSame('ALT+A', Keypress::detectAltKey("\033a"));
        self::assertNull(Keypress::detectAltKey('a'));
        self::assertIsBool(Keypress::isSupported());
    }

    public function test_terminal_emits_control_sequences(): void
    {
        $out = new BufferedOutput;
        Terminal::make($out)->bell()->tabTitle('Build')->altScreen()->moveCursor(2, 5)->clearLine();

        $written = $out->fetch();
        self::assertStringContainsString(ControlChars::Bel->char(), $written);
        self::assertStringContainsString(']0;Build', $written);
        self::assertStringContainsString("\e[?1049h", $written);
        self::assertStringContainsString("\e[2;5H", $written);
        self::assertStringContainsString("\e[2K", $written);
    }
}
