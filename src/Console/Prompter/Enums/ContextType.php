<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Enums;

enum ContextType: string
{
    case NOTE = 'note';
    case ERROR = 'error';
    case WARNING = 'warning';
    case ALERT = 'alert';
    case INFO = 'info';
    case INTRO = 'intro';
    case OUTRO = 'outro';

    public static function keysWithLabels(): array
    {
        return [
            self::NOTE->value    => 'Note',
            self::ERROR->value   => 'Error',
            self::WARNING->value => 'Warning',
            self::ALERT->value   => 'Alert',
            self::INFO->value    => 'Info',
            self::INTRO->value   => 'Intro',
            self::OUTRO->value   => 'Outro',
        ];
    }

    public static function keys(): array
    {
        $keys = [];
        foreach (self::cases() as $case) {
            $keys[$case->value] = $case->name;
        }
        return $keys;
    }

    public function label(): string
    {
        return self::keysWithLabels()[$this->value] ?? $this->name;
    }
}
