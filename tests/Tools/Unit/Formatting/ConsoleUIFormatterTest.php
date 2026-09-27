<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Unit\Formatting;

use ReflectionClass;
use BadMethodCallException;
use ReflectionClassConstant;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Support\Color;
use Symfony\Component\Console\Output\BufferedOutput;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Exceptions\InvalidColorException;

final class ConsoleUIFormatterTest extends TestCase
{
    public function test_static_message_helpers_return_strings_containing_text(): void
    {
        self::assertStringContainsString('done', ConsoleUIFormatter::success('done'));
        self::assertStringContainsString('oops', ConsoleUIFormatter::error('oops'));
        self::assertStringContainsString('careful', ConsoleUIFormatter::warning('careful'));
        self::assertStringContainsString('note', ConsoleUIFormatter::info('note'));
    }

    public function test_fluent_builder_renders_message(): void
    {
        $out = ConsoleUIFormatter::create()->addMessage('hello')->render();

        self::assertStringContainsString('hello', $out);
    }

    public function test_colorize_applies_a_background_colour(): void
    {
        $previous = getenv('FORCE_COLOR');
        putenv('FORCE_COLOR=1');

        try {
            $out = ConsoleUIFormatter::create()->colorize('x', ConsoleUIFormatter::WHITE, false, ConsoleUIFormatter::BG_RED);

            // BG_RED must resolve to the red *background* SGR, not the foreground.
            self::assertStringContainsString("\033[41m", $out);
        } finally {
            $previous === false ? putenv('FORCE_COLOR') : putenv("FORCE_COLOR={$previous}");
        }
    }

    public function test_the_fluent_chain_builds_a_padded_block(): void
    {
        $text = ConsoleUIFormatter::create()
            ->txtColorRed()->bgColorWhite()->bold()
            ->message('✓ Deployed 🚀')
            ->padding(2)
            ->lineHeight(3);

        // 13 columns of text (the rocket is two wide) plus 2 + 2 padding.
        $blank = '<fg=red;bg=white;options=bold>' . str_repeat(' ', 17) . '</>';

        self::assertSame(
            $blank . "\n<fg=red;bg=white;options=bold>  ✓ Deployed 🚀  </>\n" . $blank,
            (string) $text,
        );
    }

    public function test_line_height_puts_the_odd_line_below(): void
    {
        $lines = explode("\n", ConsoleUIFormatter::create()->bg('blue')->message('x')->lineHeight(2)->render());

        self::assertSame(['<bg=blue>x</>', '<bg=blue> </>'], $lines);
    }

    public function test_multi_line_messages_are_padded_into_a_rectangle(): void
    {
        $lines = explode("\n", ConsoleUIFormatter::create()->bg('blue')->message("ab\nabcd")->render());

        self::assertSame(['<bg=blue>ab  </>', '<bg=blue>abcd</>'], $lines);
    }

    public function test_a_plain_message_renders_as_before(): void
    {
        self::assertSame('<fg=green>hello</>', ConsoleUIFormatter::create()->message('hello')->fg('green')->render());
        self::assertSame('hello', ConsoleUIFormatter::create()->message('hello')->render());
    }

    public function test_every_declared_colour_method_resolves(): void
    {
        preg_match_all('/@method self ((?:txt|bg)Color\w+)\(\)/', (string) new ReflectionClass(ConsoleUIFormatter::class)->getDocComment(), $m);

        // A sweep over nothing passes; pin the size of what was inspected.
        self::assertCount(76, $m[1]);

        foreach ($m[1] as $method) {
            $rendered = ConsoleUIFormatter::create()->message('x')->{$method}()->render();

            // Must survive a real formatter, which throws on anything it does not know.
            self::assertSame('x', new OutputFormatter(false)->format($rendered), $method);
        }
    }

    public function test_every_known_colour_name_has_a_declared_method(): void
    {
        $doc = (string) new ReflectionClass(ConsoleUIFormatter::class)->getDocComment();
        $named = array_keys(new ReflectionClassConstant(Color::class, 'NAMES')->getValue());
        $names = array_unique([...ConsoleUIFormatter::SYMFONY_COLORS, ...$named]);

        self::assertGreaterThan(30, count($names));

        foreach ($names as $name) {
            $studly = str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));

            self::assertStringContainsString("@method self txtColor{$studly}()", $doc, $name);
            self::assertStringContainsString("@method self bgColor{$studly}()", $doc, $name);
        }
    }

    public function test_bright_colour_constants_render(): void
    {
        $constants = array_filter(
            new ReflectionClass(ConsoleUIFormatter::class)->getConstants(),
            static fn (string $name): bool => str_contains($name, 'BRIGHT_'),
            ARRAY_FILTER_USE_KEY,
        );

        self::assertCount(14, $constants);

        foreach ($constants as $name => $color) {
            $rendered = ConsoleUIFormatter::create()->message('x')->fg($color)->bg($color)->render();

            self::assertSame("<fg={$color};bg={$color}>x</>", $rendered, $name);
            self::assertStringContainsString('x', new OutputFormatter(true)->format($rendered));
        }
    }

    public function test_non_symfony_colours_are_converted_to_hex(): void
    {
        self::assertSame('<fg=#ff8800>x</>', ConsoleUIFormatter::create()->message('x')->txtColorOrange()->render());
        self::assertSame('<bg=#7c3aed>x</>', ConsoleUIFormatter::create()->message('x')->bg('#7C3AED')->render());
    }

    public function test_unknown_colours_and_methods_fail_where_they_are_set(): void
    {
        $this->expectException(InvalidColorException::class);

        ConsoleUIFormatter::create()->txtColorNotAColour();
    }

    public function test_unknown_methods_are_not_swallowed(): void
    {
        $this->expectException(BadMethodCallException::class);

        ConsoleUIFormatter::create()->frobnicate();
    }

    public function test_unknown_text_styles_fail_where_they_are_set(): void
    {
        self::assertSame('<options=underscore>x</>', ConsoleUIFormatter::create()->message('x')->addTextStyles('underline')->render());

        $this->expectException(InvalidArgumentException::class);

        ConsoleUIFormatter::create()->addTextStyles('italic');
    }

    public function test_message_markup_is_shown_literally_unless_allowed(): void
    {
        $formatter = new OutputFormatter(true);

        $escaped = $formatter->format(ConsoleUIFormatter::create()->fg('green')->message('50% </> <fg=red>done')->render());
        self::assertStringContainsString('50% </> <fg=red>done', (string) $escaped);
        self::assertStringNotContainsString("\033[31m", (string) $escaped);

        $allowed = $formatter->format(ConsoleUIFormatter::create()->message('a <fg=red>b</>')->markup()->render());
        self::assertStringContainsString("\033[31mb", (string) $allowed);
    }

    public function test_a_coloured_link_keeps_its_colour(): void
    {
        $rendered = ConsoleUIFormatter::create()->message('docs')->fg('green')->href('https://example.com/a')->render();

        self::assertSame('<fg=green;href=https://example.com/a>docs</>', $rendered);
    }

    public function test_colorize_accepts_the_bright_constants(): void
    {
        $previous = getenv('FORCE_COLOR');
        putenv('FORCE_COLOR=1');

        try {
            $out = ConsoleUIFormatter::create()->colorize('x', ConsoleUIFormatter::BRIGHT_RED, false, ConsoleUIFormatter::BG_BRIGHT_BLUE);

            self::assertStringContainsString("\033[91m", $out);
            self::assertStringContainsString("\033[104m", $out);
        } finally {
            $previous === false ? putenv('FORCE_COLOR') : putenv("FORCE_COLOR={$previous}");
        }
    }

    public function test_it_writes_and_converts_to_ansi(): void
    {
        $text = ConsoleUIFormatter::create()->fg('red')->message('x');

        $output = new BufferedOutput(decorated: false);
        $text->write($output);
        self::assertSame('x' . PHP_EOL, $output->fetch());

        self::assertSame("\033[31mx\033[39m", $text->toAnsi(Capabilities::fake(colors: true)));
        self::assertSame('x', $text->toAnsi(Capabilities::fake(colors: false)));
    }

    public function test_padding_and_line_height_reject_nonsense(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ConsoleUIFormatter::create()->lineHeight(0);
    }

    public function test_reset_clears_the_layout_too(): void
    {
        $formatter = ConsoleUIFormatter::create()->message('x')->padding(3)->lineHeight(3)->markup();

        self::assertSame('y', $formatter->reset()->message('y')->render());
    }

    public function test_the_full_chain_with_spacing_and_emoji(): void
    {
        $text = ConsoleUIFormatter::create()
            ->capabilities(Capabilities::fake(unicode: true))
            ->txtColorRed()->bgColorWhite()->bold()
            ->message('✓ Deployed 🚀')
            ->padding(2)
            ->lineHeight(3)
            ->addSpaceBefore()
            ->addSpaceAfter(2, ConsoleUIFormatter::TAB);

        $blank = ' <fg=red;bg=white;options=bold>' . str_repeat(' ', 17) . "</>\t\t";

        self::assertSame(
            $blank . "\n <fg=red;bg=white;options=bold>  ✓ Deployed 🚀  </>\t\t\n" . $blank,
            (string) $text,
        );
    }

    public function test_spacing_calls_add_up_and_sit_outside_the_background(): void
    {
        $rendered = ConsoleUIFormatter::create()->bg('blue')->message('x')
            ->addSpaceBefore(2)->addSpaceBefore(1, ConsoleUIFormatter::TAB)
            ->addSpaceAfter(3)
            ->render();

        self::assertSame("  \t<bg=blue>x</>   ", $rendered);
    }

    public function test_spacing_must_be_positive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ConsoleUIFormatter::create()->addSpaceBefore(0);
    }

    public function test_spacing_accepts_only_space_or_tab(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ConsoleUIFormatter::create()->addSpaceAfter(1, '-');
    }

    public function test_message_resolves_shortcodes_and_icon_prefixes_an_emoji(): void
    {
        $unicode = ConsoleUIFormatter::create()->capabilities(Capabilities::fake(unicode: true));
        self::assertSame('🚀 Deployed 🎉', $unicode->icon('rocket')->message('Deployed :tada:')->render());

        // ASCII fallback, escaped so `->` cannot read as a tag.
        $ascii = ConsoleUIFormatter::create()->capabilities(Capabilities::fake(unicode: false));
        self::assertSame('-\\> Deployed \\o/', $ascii->icon('rocket')->message('Deployed :tada:')->render());

        // addMessage() keeps the text exactly as given.
        self::assertSame(':tada:', ConsoleUIFormatter::create()->addMessage(':tada:')->render());
        self::assertSame('x', ConsoleUIFormatter::create()->icon('no-such-emoji-name')->message('x')->render());
    }

    public function test_reset_clears_spacing_and_icon(): void
    {
        $formatter = ConsoleUIFormatter::create()->icon('rocket')->message('x')->addSpaceBefore()->addSpaceAfter();

        self::assertSame('y', $formatter->reset()->message('y')->render());
    }
}
