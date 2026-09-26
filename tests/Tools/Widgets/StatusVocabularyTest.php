<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use RuntimeException;
use Illuminate\Contracts\Console\Kernel;
use Simtabi\Laranail\Console\Tools\Support\Status;
use Simtabi\Laranail\Console\Tools\Tests\TestCase;
use Simtabi\Laranail\Console\Tools\Commands\Command;
use Symfony\Component\Console\Output\BufferedOutput;
use Simtabi\Laranail\Console\Tools\Widgets\CheckList;
use Symfony\Component\Console\Output\OutputInterface;
use Simtabi\Laranail\Console\Tools\Widgets\MetricTable;
use Simtabi\Laranail\Console\Tools\Widgets\StatusBadge;
use Simtabi\Laranail\Console\Tools\Support\Capabilities;
use Simtabi\Laranail\Console\Tools\Support\ExceptionRenderer;
use Simtabi\Laranail\Console\Tools\Widgets\TaskProgress\TaskStatus;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\ConfirmsDestructiveActions;

/**
 * The shared status vocabulary and the widgets built on it: one definition of
 * glyph, colour and translated label per state, so no consumer hand-writes a
 * `match` from a status string to a styled label.
 */
final class StatusVocabularyTest extends TestCase
{
    protected function tearDown(): void
    {
        Capabilities::clearFake();

        parent::tearDown();
    }

    public function test_every_status_label_resolves_through_the_translator(): void
    {
        $cases = Status::cases();

        // A sweep over nothing passes; pin the size of what was inspected.
        self::assertCount(9, $cases);

        foreach ($cases as $status) {
            $key = 'laranail-console::console.status.' . $status->value;

            self::assertNotSame($key, trans($key), "status.{$status->value} has no translation");
            self::assertSame(trans($key), $status->label());
        }
    }

    public function test_labels_follow_the_configured_locale(): void
    {
        app('translator')->addLines(['console.status.failed' => 'Échoué'], 'fr', 'laranail-console');
        config(['laranail.console.locale' => 'fr']);

        self::assertSame('Échoué', Status::Failed->label());
        self::assertSame('en', app()->getLocale());
    }

    public function test_symbols_follow_capabilities(): void
    {
        $fancy = Capabilities::fake(unicode: true);
        self::assertSame('✓', Status::Success->symbol($fancy));
        self::assertSame('✗', Status::Failed->symbol($fancy));
        self::assertSame('', Status::Unknown->symbol($fancy));

        $ascii = Capabilities::fake(unicode: false);
        self::assertSame('[OK]', Status::Success->symbol($ascii));
        self::assertSame('[X]', Status::Failed->symbol($ascii));

        // On/off states read as on/off in ASCII, and never collide with Pending.
        self::assertSame('[on]', Status::Active->symbol($ascii));
        self::assertSame('[off]', Status::Inactive->symbol($ascii));
        self::assertNotSame(Status::Pending->symbol($ascii), Status::Inactive->symbol($ascii));
        self::assertSame('●', Status::Active->symbol($fancy));
    }

    public function test_from_bool(): void
    {
        self::assertSame(Status::Success, Status::fromBool(true));
        self::assertSame(Status::Failed, Status::fromBool(false));
    }

    public function test_every_task_status_maps_onto_the_shared_vocabulary(): void
    {
        foreach (TaskStatus::cases() as $task) {
            self::assertInstanceOf(Status::class, $task->toStatus());
        }

        self::assertSame(Status::Running, TaskStatus::Running->toStatus());
    }

    public function test_badge_renders_glyph_label_and_colour(): void
    {
        $caps = Capabilities::fake(unicode: true);

        $badge = StatusBadge::of(Status::Success)->capabilities($caps)->render();
        self::assertStringContainsString('✓ Completed', $badge);
        self::assertStringContainsString('green', $badge);

        self::assertStringContainsString('✗ Failed', StatusBadge::of(false)->capabilities($caps)->render());
        self::assertStringContainsString('Up to date', StatusBadge::of(Status::Success)->label('Up to date')->render());
        self::assertStringNotContainsString('✓', StatusBadge::of(Status::Success)->withoutSymbol()->render());
    }

    public function test_badge_from_a_domain_map_falls_back_to_unknown(): void
    {
        $map = ['up-to-date' => Status::Success, 'unreachable' => Status::Failed];

        self::assertSame(Status::Success, StatusBadge::fromMap($map, 'up-to-date')->status());
        self::assertSame(Status::Unknown, StatusBadge::fromMap($map, 'never-heard-of-it')->status());
        self::assertSame(Status::Unknown, StatusBadge::fromMap($map, null)->status());
    }

    public function test_badge_can_keep_the_domain_value_as_its_label(): void
    {
        $caps = Capabilities::fake(unicode: true);
        $map = ['up-to-date' => Status::Success];

        self::assertStringContainsString('✓ up-to-date', StatusBadge::fromMap($map, 'up-to-date', valueAsLabel: true)->capabilities($caps)->render());
        self::assertStringContainsString('odd-state', StatusBadge::fromMap($map, 'odd-state', valueAsLabel: true)->render());
        self::assertStringContainsString('Completed', StatusBadge::fromMap($map, 'up-to-date')->render());
    }

    public function test_table_cells_keep_markup_colour_on_a_decorated_output(): void
    {
        $decorated = new BufferedOutput(decorated: true);
        MetricTable::make()->metric('State', StatusBadge::of(true)->render())->render($decorated);
        self::assertStringContainsString("\e[", $decorated->fetch());

        $plain = new BufferedOutput(decorated: false);
        MetricTable::make()->metric('State', StatusBadge::of(true)->render())->render($plain);
        $text = $plain->fetch();
        self::assertStringNotContainsString("\e[", $text);
        self::assertStringNotContainsString('<fg=', $text);
    }

    public function test_metric_pairs_keep_rows_with_identical_labels(): void
    {
        $out = MetricTable::make()->metrics([['Size', '1 KB'], ['Size', '2 KB']])->render();

        self::assertStringContainsString('1 KB', $out);
        self::assertStringContainsString('2 KB', $out);
    }

    public function test_badge_label_is_sanitized(): void
    {
        self::assertStringNotContainsString("\x1b[2J", StatusBadge::of(true)->label("ok\x1b[2J")->render());
    }

    public function test_checklist_aligns_labels_and_reads_pass_fail(): void
    {
        $caps = Capabilities::fake(unicode: true);

        $list = CheckList::make('Readiness', $caps)
            ->check('Tables', true)
            ->check('Search index', false, 'run the seeder')
            ->check('Queue', Status::Skipped);

        $plain = strip_tags($list->render());

        self::assertStringContainsString('Readiness', $plain);
        self::assertStringContainsString('✓ Tables:       OK', $plain);
        self::assertStringContainsString('✗ Search index: NOT READY run the seeder', $plain);
        self::assertStringContainsString('Queue:        Skipped', $plain);
        self::assertFalse($list->passes());
        self::assertTrue(CheckList::make()->check('A', true)->check('B', Status::Skipped)->passes());
    }

    public function test_checklist_words_are_overridable_and_ascii_safe(): void
    {
        $caps = Capabilities::fake(unicode: false);

        $plain = strip_tags(CheckList::make(null, $caps)->check('Db', true)->passLabel('READY')->render());

        self::assertStringContainsString('[OK] Db: READY', $plain);
        self::assertSame('', CheckList::make()->render());
    }

    public function test_metric_table_formats_values_under_translated_headers(): void
    {
        $out = MetricTable::make()
            ->metric('Total icons', 121314)
            ->metrics(['Ratio' => 0.5, 'Enabled' => true, 'Driver' => null])
            ->render();

        self::assertStringContainsString('Metric', $out);
        self::assertStringContainsString('Value', $out);
        self::assertStringContainsString('121,314', $out);
        self::assertStringContainsString('0.5', $out);
        self::assertStringContainsString('Yes', $out);
        self::assertStringContainsString('—', $out);
        self::assertStringContainsString('Count', MetricTable::make()->headers(value: 'Count')->metric('x', 1)->render());
        self::assertSame('', MetricTable::make()->render());
    }

    public function test_exception_renderer_escalates_detail_with_verbosity(): void
    {
        $e = new RuntimeException('token=secret leaked into a frame');

        foreach ([
            [OutputInterface::VERBOSITY_NORMAL, false, false],
            [OutputInterface::VERBOSITY_VERBOSE, true, false],
            [OutputInterface::VERBOSITY_VERY_VERBOSE, true, false],
            [OutputInterface::VERBOSITY_DEBUG, true, true],
        ] as [$level, $file, $trace]) {
            $out = new BufferedOutput($level);
            ExceptionRenderer::make($out)->context('Import failed')->render($e);
            $text = $out->fetch();

            self::assertStringContainsString('Import failed: token=secret', $text);
            self::assertSame($file, str_contains($text, 'File: '), "file line at verbosity {$level}");
            self::assertSame($trace, str_contains($text, 'Trace: '), "trace at verbosity {$level}");
        }
    }

    public function test_command_exceptions_render_through_the_same_policy(): void
    {
        $this->app->make(Kernel::class)->registerCommand(new class extends Command
        {
            protected $signature = 'laranail-test:status-explode';

            public function handle(): int
            {
                $this->handleException(new RuntimeException('boom'));

                return self::FAILURE;
            }
        });

        $this->artisan('laranail-test:status-explode')
            ->expectsOutputToContain('Command failed: boom')
            ->doesntExpectOutputToContain('Trace:')
            ->assertExitCode(1);
    }

    public function test_force_answers_yes_without_prompting(): void
    {
        $this->registerDestructive('laranail-test:status-forceable {--force}');

        $this->artisan('laranail-test:status-forceable', ['--force' => true])
            ->expectsOutputToContain('dropped')
            ->assertExitCode(0);
    }

    public function test_declining_reports_cancellation_as_success(): void
    {
        $this->registerDestructive('laranail-test:status-declinable {--force}');

        $this->artisan('laranail-test:status-declinable')
            ->expectsConfirmation('Drop everything?', 'no')
            ->expectsOutputToContain('Operation cancelled.')
            ->doesntExpectOutputToContain('dropped')
            ->assertExitCode(0);
    }

    public function test_a_command_without_a_force_option_still_prompts(): void
    {
        $this->registerDestructive('laranail-test:status-optionless');

        $this->artisan('laranail-test:status-optionless')
            ->expectsConfirmation('Drop everything?', 'yes')
            ->expectsOutputToContain('dropped')
            ->assertExitCode(0);
    }

    private function registerDestructive(string $signature): void
    {
        $command = new class($signature) extends Command
        {
            use ConfirmsDestructiveActions;

            public function __construct(string $signature)
            {
                $this->signature = $signature;

                parent::__construct();
            }

            public function handle(): int
            {
                if (! $this->confirmDestructive('Drop everything?', yes: 'Yes, drop it', no: 'Keep it')) {
                    return $this->cancelled();
                }

                $this->line('dropped');

                return self::SUCCESS;
            }
        };

        $this->app->make(Kernel::class)->registerCommand($command);
    }
}
