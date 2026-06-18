<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Services\Components;

use Laravel\Prompts\Note;
use Simtabi\Laranail\Console\Prompter\Enums\ContextType;
use Simtabi\Laranail\Console\Prompter\Exceptions\PrompterException;

/**
 * Class ContextService
 *
 * This class manages context-related methods.
 *
 * @method void note(string $message, ?string $type = null)
 * @method void error(string $message)
 * @method void warning(string $message)
 * @method void alert(string $message)
 * @method void info(string $message)
 * @method void intro(string $message)
 * @method void outro(string $message)
 */
class ContextBuilderService
{
    /**
     * @var array<string, callable>
     */
    private array $contexts = [];

    /**
     * Constructor to initialize context methods.
     */
    public function __construct()
    {
        $this->initializeContexts();
    }

    /**
     * Initialize context methods based on ContextType enum.
     */
    private function initializeContexts(): void
    {
        foreach (ContextType::cases() as $type) {
            $this->contexts[$type->value] = $this->createContextMethod($type);
        }
    }

    /**
     * Create a context method.
     *
     * @param ContextType $type
     * @return callable
     */
    private function createContextMethod(ContextType $type): callable
    {
        return function (string $message) use ($type): void {
            (new Note($message, $type->value))->display();
        };
    }

    /**
     * Magic method to dynamically call context methods.
     *
     * @param string $method The name of the method.
     * @param array $arguments The arguments to pass to the method.
     * @return mixed The result of the method call.
     *
     * @throws PrompterException If the context method does not exist.
     */
    public function __call(string $method, array $arguments): mixed
    {
        if (isset($this->contexts[$method])) {
            return $this->contexts[$method](...$arguments);
        }

        throw PrompterException::badMethodCall([
            'method'  => $method,
            'methods' => rtrim(implode(', ', array_keys($this->contexts)), ', '),
        ]);
    }
}
