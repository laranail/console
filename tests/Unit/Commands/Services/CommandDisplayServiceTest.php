<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Tests\Unit\Commands\Services;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\ConsoleTools\Commands\Services\CommandDisplayService;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\BufferedOutput;

final class CommandDisplayServiceTest extends TestCase
{
    private BufferedOutput $output;

    private CommandDisplayService $display;

    protected function setUp(): void
    {
        parent::setUp();

        $this->output = new BufferedOutput;
        $this->display = new CommandDisplayService($this->output);
    }

    public function test_message_helpers_write_the_message_text(): void
    {
        $this->display->success('saved');
        $this->display->warning('careful');
        $this->display->error('broken');
        $this->display->info('noted');

        $out = $this->output->fetch();

        self::assertStringContainsString('saved', $out);
        self::assertStringContainsString('careful', $out);
        self::assertStringContainsString('broken', $out);
        self::assertStringContainsString('noted', $out);
    }

    public function test_format_bytes_scales_units(): void
    {
        // The loop divides only while bytes are strictly greater than 1024,
        // so exactly 1024 stays in bytes.
        self::assertSame('512 B', $this->display->formatBytes(512));
        self::assertSame('1024 B', $this->display->formatBytes(1024));
        self::assertSame('1.5 KB', $this->display->formatBytes(1536));
        self::assertSame('1024 KB', $this->display->formatBytes(1024 * 1024));
        self::assertSame('1.5 MB', $this->display->formatBytes((int) (1.5 * 1024 * 1024)));
    }

    public function test_separator_repeats_character(): void
    {
        $this->display->separator('-', 5);

        self::assertStringContainsString('-----', $this->output->fetch());
    }

    public function test_header_wraps_title_with_separators(): void
    {
        $this->display->header('Title', '*');

        $out = $this->output->fetch();
        self::assertStringContainsString('Title', $out);
        self::assertStringContainsString(str_repeat('*', 50), $out);
    }

    public function test_list_renders_each_item_with_optional_title(): void
    {
        $this->display->list(['one', 'two'], 'Items');

        $out = $this->output->fetch();
        self::assertStringContainsString('Items', $out);
        self::assertStringContainsString('one', $out);
        self::assertStringContainsString('two', $out);
    }

    public function test_key_value_renders_pairs(): void
    {
        $this->display->keyValue(['name' => 'ada', 'role' => 'eng']);

        $out = $this->output->fetch();
        self::assertStringContainsString('name', $out);
        self::assertStringContainsString('ada', $out);
        self::assertStringContainsString('role', $out);
    }

    public function test_display_table_renders_headers_and_rows(): void
    {
        $this->display->displayTable(['Col'], [['cell-value']]);

        $out = $this->output->fetch();
        self::assertStringContainsString('Col', $out);
        self::assertStringContainsString('cell-value', $out);
    }

    public function test_show_progress_bar_returns_configured_progress_bar(): void
    {
        $bar = $this->display->showProgressBar(10, 'Loading');

        self::assertInstanceOf(ProgressBar::class, $bar);
        self::assertSame(10, $bar->getMaxSteps());
    }
}
