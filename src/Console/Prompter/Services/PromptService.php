<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Services;

use BadMethodCallException;
use Closure;
use Illuminate\Support\Collection;
use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\FormBuilder;
use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\PausePrompt;
use Laravel\Prompts\Progress;
use Laravel\Prompts\SearchPrompt;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\Spinner;
use Laravel\Prompts\SuggestPrompt;
use Laravel\Prompts\Table;
use Laravel\Prompts\TextareaPrompt;
use Laravel\Prompts\TextPrompt;
use Simtabi\Laranail\Console\Prompter\Exceptions\PrompterException;
use Simtabi\Laranail\Console\Prompter\Services\Components\ContextBuilderService;

/**
 * Class PromptManager
 *
 * This class manages various prompt types for user input.
 *
 * Usage example:
 * $prompts = new PromptManager(
 *     new TextPrompt(),
 *     new TextareaPrompt(),
 *     new PasswordPrompt(),
 *     new SelectPrompt(),
 *     new MultiSelectPrompt(),
 *     new ConfirmPrompt(),
 *     new PausePrompt(),
 *     new SuggestPrompt(),
 *     new SearchPrompt(),
 *     new MultiSearchPrompt(),
 *     new Spinner(),
 *     new Table(),
 *     new Progress(),
 *     new FormBuilder(),
 *     new ContextBuilder()
 * );
 * $userName = $prompts->text('Enter your name');
 *
 * @method string text(string $label, string $placeholder = '', string $default = '', bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method string textarea(string $label, string $placeholder = '', string $default = '', bool|string $required = false, ?Closure $validate = null, string $hint = '', int $rows = 5)
 * @method string password(string $label, string $placeholder = '', bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method int|string select(string $label, array|Collection $options, int|string|null $default = null, int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true)
 * @method array multiselect(string $label, array|Collection $options, array|Collection $default = [], int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.')
 * @method bool confirm(string $label, bool $default = true, string $yes = 'Yes', string $no = 'No', bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method bool pause(string $message = 'Press enter to continue...')
 * @method string suggest(string $label, array|Collection|Closure $options, string $placeholder = '', string $default = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method int|string search(string $label, Closure $options, string $placeholder = '', int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true)
 * @method array multisearch(string $label, Closure $options, string $placeholder = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.')
 * @method mixed spin(Closure $callback, string $message = '')
 * @method void table(array|Collection $headers = [], array|Collection|null $rows = null)
 * @method Progress|array progress(string $label, iterable|int $steps, ?Closure $callback = null, string $hint = '')
 * @method FormBuilder form()
 */
class PromptService
{
    public const string TEXT = 'text';

    public const string TEXTAREA = 'textarea';

    public const string PASSWORD = 'password';

    public const string SELECT = 'select';

    public const string MULTISELECT = 'multiselect';

    public const string CONFIRM = 'confirm';

    public const string PAUSE = 'pause';

    public const string SUGGEST = 'suggest';

    public const string SEARCH = 'search';

    public const string MULTISEARCH = 'multisearch';

    public const string SPIN = 'spin';

    public const string TABLE = 'table';

    public const string PROGRESS = 'progress';

    public const string FORM = 'form';

    /**
     * @var array<string, callable>
     */
    protected array $methods;

    protected ContextBuilderService $contextBuilder;

    /**
     * Constructor to initialize prompt methods and context service.
     */
    public function __construct()
    {
        $this->methods = [
            self::TEXT => fn (string $label, string $placeholder = '', string $default = '', bool|string $required = false, mixed $validate = null, string $hint = ''): string => (new TextPrompt($label, $placeholder, $default, $required, $validate, $hint))->prompt(),

            self::TEXTAREA => fn (string $label, string $placeholder = '', string $default = '', bool|string $required = false, ?Closure $validate = null, string $hint = '', int $rows = 5): string => (new TextareaPrompt($label, $placeholder, $default, $required, $validate, $hint, $rows))->prompt(),

            self::PASSWORD => fn (string $label, string $placeholder = '', bool|string $required = false, mixed $validate = null, string $hint = ''): string => (new PasswordPrompt($label, $placeholder, $required, $validate, $hint))->prompt(),

            self::SELECT => fn (string $label, array|Collection $options, int|string|null $default = null, int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true): int|string => (new SelectPrompt($label, $options, $default, $scroll, $validate, $hint, $required))->prompt(),

            self::MULTISELECT => fn (string $label, array|Collection $options, array|Collection $default = [], int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.'): array => (new MultiSelectPrompt($label, $options, $default, $scroll, $required, $validate, $hint))->prompt(),

            self::CONFIRM => fn (string $label, bool $default = true, string $yes = 'Yes', string $no = 'No', bool|string $required = false, mixed $validate = null, string $hint = ''): bool => (new ConfirmPrompt($label, $default, $yes, $no, $required, $validate, $hint))->prompt(),

            self::PAUSE => fn (string $message = 'Press enter to continue...'): bool => (new PausePrompt($message))->prompt(),

            self::SUGGEST => fn (string $label, array|Collection|Closure $options, string $placeholder = '', string $default = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = ''): string => (new SuggestPrompt($label, $options, $placeholder, $default, $scroll, $required, $validate, $hint))->prompt(),

            self::SEARCH => fn (string $label, Closure $options, string $placeholder = '', int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true): int|string => (new SearchPrompt($label, $options, $placeholder, $scroll, $validate, $hint, $required))->prompt(),

            self::MULTISEARCH => fn (string $label, Closure $options, string $placeholder = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.'): array => (new MultiSearchPrompt($label, $options, $placeholder, $scroll, $required, $validate, $hint))->prompt(),

            self::SPIN => fn (Closure $callback, string $message = ''): mixed => (new Spinner($message))->spin($callback),

            self::TABLE => function (array|Collection $headers = [], array|Collection|null $rows = null): void {
                (new Table($headers, $rows))->display();
            },

            self::PROGRESS => fn (string $label, iterable|int $steps, ?Closure $callback = null, string $hint = ''): array => (new Progress($label, $steps, $hint))->map($callback),

            self::FORM => fn (): FormBuilder => new FormBuilder,
        ];

        $this->contextBuilder = new ContextBuilderService;
    }

    /**
     * Magic method to dynamically call prompt methods.
     *
     * @param string $method The name of the method.
     * @param array $arguments The arguments to pass to the method.
     * @return mixed The result of the method call.
     *
     * @throws BadMethodCallException|PrompterException If the method does not exist.
     */
    public function __call(string $method, array $arguments): mixed
    {
        if (isset($this->methods[$method])) {
            return $this->methods[$method](...$arguments);
        }

        throw PrompterException::triggerErrorMessage('method_does_not_exist', ['method' => $method, 'class' => static::class]);
    }

    /**
     * Provides access to context-related methods.
     *
     * @return ContextBuilderService An instance of ContextBuilder with context-related methods.
     */
    public function context(): ContextBuilderService
    {
        return $this->contextBuilder;
    }
}
