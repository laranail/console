<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Services;

use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Simtabi\Laranail\Console\Facades\Console;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\InteractsWithConsoleWriter;
use Simtabi\Laranail\Console\Tools\Services\ConsoleWriter;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleWriterTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();
        parent::tearDown();
    }

    private function writer(BufferedOutput $out): ConsoleWriter
    {
        return ConsoleWriter::make($out);
    }

    // --- output + styling ---

    public function test_line_and_lines_write_with_newlines(): void
    {
        $out = new BufferedOutput;
        $this->writer($out)->line('a')->lines('b', 'c');

        self::assertSame('a' . PHP_EOL . 'b' . PHP_EOL . 'c' . PHP_EOL, $out->fetch());
    }

    public function test_write_has_no_newline_and_newline_adds_them(): void
    {
        $out = new BufferedOutput;
        $this->writer($out)->write('x')->newLine(2);

        self::assertSame('x' . PHP_EOL . PHP_EOL, $out->fetch());
    }

    public function test_immutable_styling_returns_new_instances(): void
    {
        $base = $this->writer(new BufferedOutput);
        $red = $base->color('red');

        self::assertNotSame($base, $red);
        self::assertNull($base->getForeground());
        self::assertSame('red', $red->getForeground());
    }

    public function test_format_wraps_styles_and_escapes(): void
    {
        $w = $this->writer(new BufferedOutput);

        // fg/bg/options register a dynamic named style (cw_<hash>) on the formatter.
        $styled = $w->color('red')->bold()->format('hi');
        self::assertStringStartsWith('<cw_', $styled);
        self::assertStringEndsWith('hi</>', $styled);

        self::assertSame('<info>hi</info>', $w->style('info')->format('hi'));
        self::assertSame('hi', $w->format('hi'));
        self::assertSame('\\<x\\>', $w->escaped()->format('<x>')); // both <> escaped
    }

    public function test_inline_style_renders_ansi_on_a_decorated_output(): void
    {
        $out = new BufferedOutput(decorated: true);
        $this->writer($out)->color('green')->line('go');

        $text = $out->fetch();
        self::assertStringContainsString("\033[", $text);   // ANSI colour emitted
        self::assertStringContainsString('go', $text);
    }

    public function test_verbosity_gates_output(): void
    {
        $out = new BufferedOutput; // VERBOSITY_NORMAL
        $this->writer($out)->verbosity(OutputInterface::VERBOSITY_VERBOSE)->line('hidden');

        self::assertSame('', $out->fetch());
    }

    public function test_when_runs_only_matching_branch(): void
    {
        $out = new BufferedOutput;
        $this->writer($out)
            ->when(true, fn (ConsoleWriter $w): ConsoleWriter => $w->line('yes'))
            ->when(false, fn (ConsoleWriter $w): ConsoleWriter => $w->line('no'));

        self::assertSame('yes' . PHP_EOL, $out->fetch());
    }

    // --- context statuses ---

    public function test_statuses_render_unicode_glyphs(): void
    {
        Capabilities::fake(unicode: true);
        $out = new BufferedOutput;
        $w = $this->writer($out);

        $w->success('done')->warning('careful')->info('fyi')->note('aside')->pending('soon');

        $text = $out->fetch();
        self::assertStringContainsString('✓ done', $text);
        self::assertStringContainsString('⚠ careful', $text);
        self::assertStringContainsString('ℹ fyi', $text);
        self::assertStringContainsString('● aside', $text);
        self::assertStringContainsString('○ soon', $text);
    }

    public function test_statuses_degrade_to_ascii(): void
    {
        Capabilities::fake(unicode: false);
        $out = new BufferedOutput;

        $this->writer($out)->success('ok');

        self::assertStringContainsString('[OK] ok', $out->fetch());
    }

    public function test_error_and_danger_route_to_stderr(): void
    {
        Capabilities::fake(unicode: true);
        $err = new BufferedOutput;
        $console = new ConsoleOutput;
        $console->setErrorOutput($err);

        ConsoleWriter::make($console)->error('boom')->danger('worse');

        $captured = $err->fetch();
        self::assertStringContainsString('✗ boom', $captured);
        self::assertStringContainsString('✖ worse', $captured);
    }

    // --- emoji / symbol / prefix ---

    public function test_emoji_prefix_by_name_shortcode_and_literal(): void
    {
        Capabilities::fake(unicode: true);

        $byName = new BufferedOutput;
        $this->writer($byName)->emoji('rocket')->line('go');
        self::assertSame('🚀 go' . PHP_EOL, $byName->fetch());

        $byShortcode = new BufferedOutput;
        $this->writer($byShortcode)->emoji(':fire:')->line('hot');
        self::assertSame('🔥 hot' . PHP_EOL, $byShortcode->fetch());

        $literal = new BufferedOutput;
        $this->writer($literal)->emoji('✅')->line('ok');
        self::assertSame('✅ ok' . PHP_EOL, $literal->fetch());
    }

    public function test_symbol_prefix_from_symbols_map(): void
    {
        Capabilities::fake(unicode: true);
        $out = new BufferedOutput;

        $this->writer($out)->symbol('arrow')->line('next');

        self::assertSame('→ next' . PHP_EOL, $out->fetch());
    }

    public function test_inline_shortcodes_render_and_can_be_disabled(): void
    {
        Capabilities::fake(unicode: true);

        $on = new BufferedOutput;
        $this->writer($on)->line('Done :tada:');
        self::assertSame('Done 🎉' . PHP_EOL, $on->fetch());

        $off = new BufferedOutput;
        $this->writer($off)->emojis(false)->line('Done :tada:');
        self::assertSame('Done :tada:' . PHP_EOL, $off->fetch());
    }

    // --- first-class exposure ---

    public function test_reachable_via_facade_manager_and_helper(): void
    {
        $out = new BufferedOutput;

        self::assertInstanceOf(ConsoleWriter::class, Console::writer($out));
        self::assertInstanceOf(ConsoleWriter::class, console_writer($out));
    }

    public function test_command_trait_writes_to_command_output(): void
    {
        Capabilities::fake(unicode: true);
        $out = new BufferedOutput;

        $command = new class extends Command
        {
            use InteractsWithConsoleWriter;

            public function emit(): void
            {
                $this->consoleWriter()->success('from command');
            }
        };
        $command->setOutput(new OutputStyle(new ArrayInput([]), $out));

        $command->emit();

        self::assertStringContainsString('✓ from command', $out->fetch());
    }
}
