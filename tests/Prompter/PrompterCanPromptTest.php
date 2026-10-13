<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Tests;

use ReflectionProperty;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SelectPrompt;
use Simtabi\Laranail\Console\Prompter\Prompter;
use Symfony\Component\Console\Input\ArrayInput;

final class PrompterCanPromptTest extends TestCase
{
    private bool $shouldFallback;

    /** @var array<class-string, mixed> */
    private array $fallbacks;

    protected function setUp(): void
    {
        parent::setUp();

        // laravel/prompts keeps its fallback state in statics that only ever turn on,
        // so snapshot them and put them back: each case here sets its own.
        $this->shouldFallback = (bool) $this->fallbackFlag()->getValue();
        $this->fallbacks = (array) $this->fallbackMap()->getValue();
    }

    protected function tearDown(): void
    {
        $this->fallbackFlag()->setValue(null, $this->shouldFallback);
        $this->fallbackMap()->setValue(null, $this->fallbacks);

        parent::tearDown();
    }

    public function test_non_interactive_input_cannot_prompt_even_with_the_fallback_on(): void
    {
        $this->enableFallback();
        $input = new ArrayInput([]);
        $input->setInteractive(false);

        self::assertFalse(Prompter::canPrompt($input));
    }

    public function test_the_question_helper_fallback_can_prompt_without_a_terminal(): void
    {
        $this->enableFallback();

        self::assertTrue(Prompter::canPrompt(new ArrayInput([])));
        self::assertTrue(Prompter::canPrompt());
    }

    public function test_an_enabled_flag_without_a_registered_fallback_is_not_enough(): void
    {
        $this->fallbackFlag()->setValue(null, true);
        $this->fallbackMap()->setValue(null, []);

        self::assertSame($this->stdinIsTerminal(), Prompter::canPrompt(new ArrayInput([])));
    }

    public function test_without_the_fallback_it_follows_whether_stdin_is_a_terminal(): void
    {
        $this->fallbackFlag()->setValue(null, false);

        self::assertSame($this->stdinIsTerminal(), Prompter::canPrompt(new ArrayInput([])));
    }

    public function test_it_cannot_prompt_when_stdin_is_not_a_terminal(): void
    {
        if (DIRECTORY_SEPARATOR === '\\' || ! function_exists('proc_open')) {
            self::markTestSkipped('Needs a POSIX shell with proc_open.');
        }

        // A fresh process, as a cron job or CI step sees it: stdin is /dev/null and
        // no command has registered the fallback.
        $code = 'require $argv[1]; echo var_export(' . Prompter::class . '::canPrompt(new '
            . ArrayInput::class . '([])), true);';
        $process = proc_open(
            [PHP_BINARY, '-r', $code, dirname(__DIR__, 2) . '/vendor/autoload.php'],
            [['file', '/dev/null', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes,
        );
        self::assertIsResource($process);

        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        self::assertSame(0, proc_close($process), $stderr);
        self::assertSame('false', $stdout);
    }

    private function enableFallback(): void
    {
        SelectPrompt::fallbackUsing(static fn (SelectPrompt $prompt): string => 'answered');
        Prompt::fallbackWhen(true);
    }

    private function stdinIsTerminal(): bool
    {
        return defined('STDIN') && stream_isatty(STDIN);
    }

    private function fallbackFlag(): ReflectionProperty
    {
        return new ReflectionProperty(Prompt::class, 'shouldFallback');
    }

    private function fallbackMap(): ReflectionProperty
    {
        return new ReflectionProperty(Prompt::class, 'fallbacks');
    }
}
