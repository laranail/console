<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Exceptions;

use Exception;

class PrompterException extends Exception
{

    protected static function trans(string $key, array $variables = []): ?string
    {
        return trans("prompter::prompter.{$key}", $variables);
    }

    public static function triggerErrorMessage(string $key, array $variables = []): self
    {
        return new self(self::trans($key, $variables));
    }

    public static function badMethodCall(array $variables = []): self
    {
        return self::triggerErrorMessage('bad_method_call', $variables);
    }

}
