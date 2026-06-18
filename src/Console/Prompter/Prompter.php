<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter;

use Closure;
use Illuminate\Support\Collection;
use Laravel\Prompts\FormBuilder;
use Simtabi\Laranail\Console\Prompter\Exceptions\PrompterException;
use Simtabi\Laranail\Console\Prompter\Services\Components\ContextBuilderService;
use Simtabi\Laranail\Console\Prompter\Services\PromptService;

/**
 * Class Prompter
 *
 * This class provides a fluent interface for chaining prompt method calls.
 *
 * @method static self text(string $label, string $placeholder = '', string $default = '', bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method static self textarea(string $label, string $placeholder = '', string $default = '', bool|string $required = false, ?Closure $validate = null, string $hint = '', int $rows = 5)
 * @method static self password(string $label, string $placeholder = '', bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method static self select(string $label, array|Collection $options, int|string|null $default = null, int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true)
 * @method static self multiselect(string $label, array|Collection $options, array|Collection $default = [], int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.')
 * @method static self confirm(string $label, bool $default = true, string $yes = 'Yes', string $no = 'No', bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method static self pause(string $message = 'Press enter to continue...')
 * @method static self suggest(string $label, array|Collection|Closure $options, string $placeholder = '', string $default = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method static self search(string $label, Closure $options, string $placeholder = '', int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true)
 * @method static self multisearch(string $label, Closure $options, string $placeholder = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.')
 * @method static self spin(Closure $callback, string $message = '')
 * @method static self table(array|Collection $headers = [], array|Collection|null $rows = null)
 * @method static self progress(string $label, iterable|int $steps, ?Closure $callback = null, string $hint = '')
 */
class Prompter
{
    protected PromptService $promptManager;

    protected mixed $result = null;

    private static ?self $instance = null;

    /**
     * Prompter constructor.
     */
    private function __construct()
    {
        $this->promptManager = new PromptService;
    }

    /**
     * Get or create a singleton instance of Prompter.
     */
    public static function getInstance(): self
    {
        if (! self::$instance instanceof Prompter) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Magic method to dynamically call prompt methods.
     *
     * @param string $method The name of the method.
     * @param array $arguments The arguments to pass to the method.
     *
     * @throws PrompterException If the method does not exist.
     */
    public function __call(string $method, array $arguments): self
    {
        if (method_exists($this->promptManager, $method)) {
            $this->result = $this->promptManager->$method(...$arguments);
        } else {
            throw PrompterException::triggerErrorMessage('method_does_not_exist', ['method' => $method, 'class' => static::class]);
        }

        return $this;
    }

    /**
     * Magic static method to dynamically call prompt methods.
     *
     * @param string $method The name of the method.
     * @param array $arguments The arguments to pass to the method.
     *
     * @throws PrompterException If the method does not exist.
     */
    public static function __callStatic(string $method, array $arguments): self
    {
        return self::getInstance()->__call($method, $arguments);
    }

    /**
     * Provides access to context-related methods.
     */
    public function context(): ContextBuilderService
    {
        return $this->promptManager->context();
    }

    /**
     * Get the result of the last prompt.
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * Get the PromptManager instance.
     */
    public function getPrompts(): PromptService
    {
        return $this->promptManager;
    }

    /**
     * Return the form builder.
     */
    public function form(): FormBuilder
    {
        return $this->promptManager->form();
    }
}
