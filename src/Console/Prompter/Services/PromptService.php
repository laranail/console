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
 * Dispatches the supported Laravel Prompts types via a closure map.
 *
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
            self::TEXT => fn (string $label, string $placeholder = '', string $default = '', bool|string $required = false, mixed $validate = null, string $hint = ''): string => new TextPrompt($label, $placeholder, $default, $required, $validate, $hint)->prompt(),

            self::TEXTAREA => fn (string $label, string $placeholder = '', string $default = '', bool|string $required = false, ?Closure $validate = null, string $hint = '', int $rows = 5): string => new TextareaPrompt($label, $placeholder, $default, $required, $validate, $hint, $rows)->prompt(),

            self::PASSWORD => fn (string $label, string $placeholder = '', bool|string $required = false, mixed $validate = null, string $hint = ''): string => new PasswordPrompt($label, $placeholder, $required, $validate, $hint)->prompt(),

            self::SELECT => fn (string $label, array|Collection $options, int|string|null $default = null, int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true): int|string => new SelectPrompt($label, $options, $default, $scroll, $validate, $hint, $required)->prompt(),

            self::MULTISELECT => fn (string $label, array|Collection $options, array|Collection $default = [], int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.'): array => new MultiSelectPrompt($label, $options, $default, $scroll, $required, $validate, $hint)->prompt(),

            self::CONFIRM => fn (string $label, bool $default = true, string $yes = 'Yes', string $no = 'No', bool|string $required = false, mixed $validate = null, string $hint = ''): bool => new ConfirmPrompt($label, $default, $yes, $no, $required, $validate, $hint)->prompt(),

            self::PAUSE => fn (string $message = 'Press enter to continue...'): bool => new PausePrompt($message)->prompt(),

            self::SUGGEST => fn (string $label, array|Collection|Closure $options, string $placeholder = '', string $default = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = ''): string => new SuggestPrompt($label, $options, $placeholder, $default, $scroll, $required, $validate, $hint)->prompt(),

            self::SEARCH => fn (string $label, Closure $options, string $placeholder = '', int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true): int|string => new SearchPrompt($label, $options, $placeholder, $scroll, $validate, $hint, $required)->prompt(),

            self::MULTISEARCH => fn (string $label, Closure $options, string $placeholder = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.'): array => new MultiSearchPrompt($label, $options, $placeholder, $scroll, $required, $validate, $hint)->prompt(),

            self::SPIN => fn (Closure $callback, string $message = ''): mixed => new Spinner($message)->spin($callback),

            self::TABLE => function (array|Collection $headers = [], array|Collection|null $rows = null): void {
                new Table($headers, $rows)->display();
            },

            self::PROGRESS => fn (string $label, iterable|int $steps, ?Closure $callback = null, string $hint = ''): array => new Progress($label, $steps, $hint)->map($callback),

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

        // Forward anything else straight to the matching laravel/prompts helper
        // (number, clear, autocomplete, datatable, grid, task, notify, title,
        // stream, note, info, …), so the wrapper auto-tracks the full prompts API.
        $function = 'Laravel\\Prompts\\' . $method;

        if (function_exists($function)) {
            return $function(...$arguments);
        }

        throw PrompterException::triggerErrorMessage('method_does_not_exist', ['method' => $method, 'class' => static::class]);
    }

    /**
     * Whether a method is dispatchable: either a mapped prompt type or any
     * laravel/prompts helper function of the same name.
     */
    public function has(string $method): bool
    {
        return isset($this->methods[$method]) || function_exists('Laravel\\Prompts\\' . $method);
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
