<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Enums;

use Simtabi\Laranail\Console\Prompter\Contracts\ValidatorInterface;
use Simtabi\Laranail\Console\Prompter\Exceptions\PrompterException;
use Simtabi\Laranail\Console\Prompter\Services\FormBuilder\FormFieldService;
use Simtabi\Laranail\Console\Prompter\Validators\AlphanumericValidator;
use Simtabi\Laranail\Console\Prompter\Validators\AlphaValidator;
use Simtabi\Laranail\Console\Prompter\Validators\ArrayValidator;
use Simtabi\Laranail\Console\Prompter\Validators\CheckboxFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\ColorValidator;
use Simtabi\Laranail\Console\Prompter\Validators\DateFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\EmailFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\NullOrEmptyValidator;
use Simtabi\Laranail\Console\Prompter\Validators\NumberFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\ObjectValidator;
use Simtabi\Laranail\Console\Prompter\Validators\PasswordFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\PathFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\PhoneNumberValidator;
use Simtabi\Laranail\Console\Prompter\Validators\RadioFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\SelectFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\TextAreaFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\TextFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\TimeFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\UsernameValidator;
use Simtabi\Laranail\Console\Prompter\Validators\UUIDFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\UuidOrIntegerOrSlugValidator;

/**
 * Enum FieldType
 *
 * Represents various form field types.
 */
enum FieldType: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case EMAIL = 'email';
    case PASSWORD = 'password';
    case TEXTAREA = 'textarea';
    case DATE = 'date';
    case TIME = 'time';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case PATH = 'path';
    case USERNAME = 'username';
    case PHONE = 'phone';
    case COLOR = 'color';
    case NULL_OR_EMPTY = 'null_or_empty';
    case ARRAY = 'array';
    case OBJECT = 'object';
    case UUID = 'uuid';
    case ALPHA = 'alpha';
    case ALPHANUMERIC = 'alphanumeric';
    case UUID_OR_INTEGER_OR_SLUG = 'uuid_or_integer_or_slug';


    /**
     * Get all enum values as array keys with custom labels.
     *
     * @return array
     */
    public static function keysWithLabels(): array
    {
        return [
            self::TEXT->value => 'Text',
            self::NUMBER->value => 'Number',
            self::EMAIL->value => 'Email',
            self::PASSWORD->value => 'Password',
            self::TEXTAREA->value => 'Textarea',
            self::DATE->value => 'Date',
            self::TIME->value => 'Time',
            self::SELECT->value => 'Select',
            self::CHECKBOX->value => 'Checkbox',
            self::RADIO->value => 'Radio',
            self::PATH->value => 'Path',
            self::USERNAME->value => 'Username',
            self::PHONE->value => 'Phone',
            self::COLOR->value => 'Color',
            self::NULL_OR_EMPTY->value => 'Null or Empty',
            self::ARRAY->value => 'Array',
            self::OBJECT->value => 'Object',
            self::OBJECT->value => 'Object',
            self::UUID->value => 'UUID',
            self::ALPHA->value => 'Alpha',
            self::ALPHANUMERIC->value => 'Alphanumeric',
            self::UUID_OR_INTEGER_OR_SLUG->value => 'UUID or Integer or Slug',
        ];
    }

    /**
     * Get all enum values as array keys.
     *
     * @return array
     */
    public static function keys(): array
    {
        $keys = [];
        foreach (self::cases() as $case) {
            $keys[$case->value] = $case->name;
        }
        return $keys;
    }

    /**
     * Get the label for a specific enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return self::keysWithLabels()[$this->value] ?? $this->name;
    }

    /**
     * Get the default validator for a given field type.
     *
     * @param self $type The type of the form field.
     * @return ValidatorInterface
     * @throws PrompterException
     */
    public static function getDefaultValidator(self $type): ValidatorInterface
    {
        return match ($type) {
            self::TEXT => new TextFieldValidator(),
            self::NUMBER => new NumberFieldValidator(),
            self::EMAIL => new EmailFieldValidator(),
            self::PASSWORD => new PasswordFieldValidator(),
            self::TEXTAREA => new TextAreaFieldValidator(),
            self::DATE => new DateFieldValidator(),
            self::TIME => new TimeFieldValidator(),
            self::SELECT => new SelectFieldValidator([]),
            self::CHECKBOX => new CheckboxFieldValidator(),
            self::RADIO => new RadioFieldValidator([]),
            self::PATH => new PathFieldValidator(),
            self::USERNAME => new UsernameValidator(),
            self::PHONE => new PhoneNumberValidator(),
            self::COLOR => new ColorValidator(),
            self::NULL_OR_EMPTY => new NullOrEmptyValidator(),
            self::ARRAY => new ArrayValidator(),
            self::OBJECT => new ObjectValidator(),
            self::UUID => new UUIDFieldValidator(),
            self::ALPHA => new AlphaValidator(),
            self::ALPHANUMERIC => new AlphanumericValidator(),
            self::UUID_OR_INTEGER_OR_SLUG => new UuidOrIntegerOrSlugValidator(),
            default => throw PrompterException::triggerErrorMessage('unsupported_input_type', ['type' => $type]),
        };
    }

    /**
     * Get the mapped validator method.
     *
     * @param FormFieldService $formField
     * @return string
     * @throws PrompterException
     */
    public static function getValidatorMethod(FormFieldService $formField): string
    {
        $methods = [
            self::TEXT->value => 'text',
            self::NUMBER->value => 'number',
            self::EMAIL->value => 'email',
            self::PASSWORD->value => 'password',
            self::TEXTAREA->value => 'textarea',
            self::DATE->value => 'date',
            self::TIME->value => 'time',
            self::SELECT->value => 'select',
            self::CHECKBOX->value => 'checkbox',
            self::RADIO->value => 'radio',
            self::PATH->value => 'text',
            self::USERNAME->value => 'text', // Assuming form method supports text for username
            self::PHONE->value => 'text', // Assuming form method supports text for phone
            self::COLOR->value => 'text', // Assuming form method supports text for color
            self::NULL_OR_EMPTY->value => 'text', // Assuming form method supports text for null/empty
            self::ARRAY->value => 'textarea', // Assuming form method supports textarea for array
            self::OBJECT->value => 'textarea', // Assuming form method supports textarea for object
            self::UUID->value => 'text', // Assuming form method supports text for uuid
            self::ALPHA->value => 'text', // Assuming form method supports text for alpha
            self::ALPHANUMERIC->value => 'text', // Assuming form method supports text for alphanumeric
            self::UUID_OR_INTEGER_OR_SLUG->value => 'text', // Assuming form method supports text for uuid_or_integer_or_slug
        ];

        if (!isset($methods[$formField->type->value])) {
            throw PrompterException::triggerErrorMessage('unsupported_input_type', ['type' => $formField->type->value]);
        }

        return $methods[$formField->type->value];
    }
}
