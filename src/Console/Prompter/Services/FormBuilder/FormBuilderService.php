<?php declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Services\FormBuilder;

use Illuminate\Filesystem\Filesystem;
use Laravel\Prompts\FormBuilder as PromptsFormBuilder;
use Simtabi\Laranail\Console\Prompter\Enums\FieldType;
use Simtabi\Laranail\Console\Prompter\Exceptions\PrompterException;

/**
 * Class FormBuilder
 *
 * Builds a form dynamically with various input fields.
 */
class FormBuilderService
{
    protected PromptsFormBuilder $form;
    protected array $fields = [];

    /**
     * FormBuilder constructor.
     *
     * @param PromptsFormBuilder $form The form instance.
     */
    public function __construct(PromptsFormBuilder $form)
    {
        $this->form = $form;
    }

    /**
     * Add a field to the form.
     *
     * @param string $name The name of the form field.
     * @param FormFieldService $formField The configuration for the form field.
     * @return $this
     * @throws PrompterException
     */
    public function addField(string $name, FormFieldService $formField): self
    {
        if (!$formField->validator && !$formField->customValidator) {
            $formField->validator = FieldType::getDefaultValidator($formField->type);
        }
        $this->fields[$name] = $formField;
        return $this;
    }

    /**
     * Build the form by adding all configured fields.
     *
     * @return $this
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
     * Add a field to the form dynamically.
     *
     * @param string $name The name of the form field.
     * @param FormFieldService $formField The configuration for the form field.
     * @throws PrompterException
     */
    protected function addFieldToForm(string $name, FormFieldService $formField): void
    {
        $parameters = [
            'label' => $formField->label,
            'placeholder' => $formField->placeholder,
            'default' => $formField->default ?? '',
            'required' => $formField->required,
            'validate' => function($value) use ($formField) {
                if ($formField->required && empty($value)) {
                    return $formField->customErrorMessage ?? 'This field is required.';
                }
                if (!empty($value)) {
                    return $formField->customValidator ? ($formField->customValidator)($value) : $formField->validator?->validate($value);
                }
                return null;
            },
            'hint' => $formField->hint,
            'name' => $name,
        ];

        if ($formField->type === FieldType::SELECT || $formField->type === FieldType::RADIO) {
            $parameters['options'] = $formField->options ?? [];
        }

        $method = FieldType::getValidatorMethod($formField);
        $this->form->$method(...$parameters);
    }

    /**
     * Submit the form and return the collected input.
     *
     * @return array
     */
    public function submit(): array
    {
        return $this->form->submit();
    }
}
