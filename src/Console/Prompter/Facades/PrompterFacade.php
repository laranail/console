<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Facades;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Console\Prompter\Prompter;

/**
 * Class PrompterFacade
 *
 * This class provides a fluent interface for chaining prompt method calls.
 *
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter text(string $label, string $placeholder = '', string $default = '', bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter textarea(string $label, string $placeholder = '', string $default = '', bool|string $required = false, ?Closure $validate = null, string $hint = '', int $rows = 5)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter password(string $label, string $placeholder = '', bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter select(string $label, array|Collection $options, int|string|null $default = null, int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter multiselect(string $label, array|Collection $options, array|Collection $default = [], int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.')
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter confirm(string $label, bool $default = true, string $yes = 'Yes', string $no = 'No', bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter pause(string $message = 'Press enter to continue...')
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter suggest(string $label, array|Collection|Closure $options, string $placeholder = '', string $default = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = '')
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter search(string $label, Closure $options, string $placeholder = '', int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter multisearch(string $label, Closure $options, string $placeholder = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.')
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter spin(Closure $callback, string $message = '')
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter note(string $message, ?string $type = null)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter error(string $message)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter warning(string $message)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter alert(string $message)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter info(string $message)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter intro(string $message)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter outro(string $message)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter table(array|Collection $headers = [], array|Collection|null $rows = null)
 * @method static \Simtabi\Laranail\Console\Prompter\Prompter progress(string $label, iterable|int $steps, ?Closure $callback = null, string $hint = '')
 *
 * @see \Simtabi\Laranail\Console\Prompter\Prompter
 */
class PrompterFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Prompter::class;
    }
}
