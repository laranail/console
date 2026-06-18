<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Services\FormBuilder;

use Laravel\Prompts\FormBuilder as PromptsFormBuilder;
use Simtabi\Laranail\Console\Prompter\Enums\FieldType;
use Simtabi\Laranail\Console\Prompter\Exceptions\PrompterException;
use Simtabi\Laranail\Console\Prompter\Validators\RadioFieldValidator;
use Simtabi\Laranail\Console\Prompter\Validators\SelectFieldValidator;

/**
 * Builds a Laravel Prompts form dynamically from chainable field definitions.
 */
class FormBuilderService
{
    protected PromptsFormBuilder $form;

    /** @var array<string, FormFieldService> */
    protected array $fields = [];

    public function __construct(PromptsFormBuilder $form)
    {
        $this->form = $form;
    }

    /**
     * Add a field configuration to the form.
     *
     * @throws PrompterException
     */
    public function addField(string $name, FormFieldService $formField): self
    {
        if (! $formField->validator && ! $formField->customValidator) {
            $formField->validator = $this->resolveDefaultValidator($formField);
        }

        $this->fields[$name] = $formField;

        return $this;
    }

    /**
     * Realise every configured field on the underlying form.
     *
     * @throws PrompterException
     */
    public function build(): self
    {
        foreach ($this->fields as $name => $formField) {
            $this->addFieldToForm($name, $formField);
        }

        return $this;
    }

    /**
     * Submit the form and return the collected input.
     *
     * @return array<string, mixed>
     */
    public function submit(): array
    {
        return $this->form->submit();
    }

    /**
     * Resolve the default validator, supplying options for choice fields so the
     * validator is actually usable (an empty option set would reject everything).
     *
     * @throws PrompterException
     */
    protected function resolveDefaultValidator(FormFieldService $formField): \Simtabi\Laranail\Console\Prompter\Contracts\ValidatorInterface
    {
        return match ($formField->type) {
            FieldType::SELECT => new SelectFieldValidator($formField->options ?? []),
            FieldType::RADIO  => new RadioFieldValidator($formField->options ?? []),
            default           => FieldType::getDefaultValidator($formField->type),
        };
    }

    /**
     * Add one field to the underlying form, calling the resolved prompt method
     * with only the named arguments that method accepts.
     *
     * @throws PrompterException
     */
    protected function addFieldToForm(string $name, FormFieldService $formField): void
    {
        $method   = FieldType::getValidatorMethod($formField);
        $validate = $this->makeValidator($formField);

        $common = [
            'label'    => $formField->label,
            'required' => $formField->required,
            'validate' => $validate,
            'hint'     => $formField->hint,
            'name'     => $name,
        ];

        $parameters = match ($method) {
            'text', 'textarea' => $common + [
                'placeholder' => $formField->placeholder,
                'default'     => $formField->default ?? '',
            ],
            'password' => $common + [
                'placeholder' => $formField->placeholder,
            ],
            'confirm' => $common + [
                'default' => (bool) $formField->default,
            ],
            'select' => $common + [
                'options' => $formField->options ?? [],
                'default' => $formField->default,
            ],
            default => $common,
        };

        $this->form->{$method}(...$parameters);
    }

    /**
     * Build the per-field validation closure shared by every prompt method.
     */
    protected function makeValidator(FormFieldService $formField): \Closure
    {
        return static function (mixed $value) use ($formField): ?string {
            $isEmpty = $value === null || $value === '' || $value === [];

            if ($formField->required && $isEmpty) {
                return $formField->customErrorMessage ?? __('console::prompter.field_required');
            }

            if ($isEmpty) {
                return null;
            }

            if ($formField->customValidator) {
                return ($formField->customValidator)($value);
            }

            return $formField->validator?->validate($value);
        };
    }
}
