<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Prompter\Tests\FormBuilder;

use Laravel\Prompts\FormBuilder as PromptsFormBuilder;
use Simtabi\Laranail\Console\Prompter\Enums\FieldType;
use Simtabi\Laranail\Console\Prompter\Services\FormBuilder\FormBuilderService;
use Simtabi\Laranail\Console\Prompter\Services\FormBuilder\FormFieldService;
use Simtabi\Laranail\Console\Prompter\Tests\TestCase;

final class FormBuilderTest extends TestCase
{
    /**
     * B1: every field type maps to a method that actually exists on
     * Laravel\Prompts\FormBuilder.
     */
    public function test_every_field_type_maps_to_a_real_formbuilder_method(): void
    {
        foreach (FieldType::cases() as $type) {
            $field = (new FormFieldService($type))->label('x');
            $method = FieldType::getValidatorMethod($field);

            self::assertTrue(
                method_exists(PromptsFormBuilder::class, $method),
                "{$type->value} maps to non-existent FormBuilder::{$method}()",
            );
        }
    }

    /**
     * B2: building a form with every field type does not throw (no unknown
     * named-argument errors).
     */
    public function test_building_all_field_types_does_not_throw(): void
    {
        $service = new FormBuilderService(new PromptsFormBuilder());

        foreach (FieldType::cases() as $i => $type) {
            $field = (new FormFieldService($type))->label('Field ' . $type->value);

            if (in_array($type, [FieldType::SELECT, FieldType::RADIO], true)) {
                $field->options(['a' => 'A', 'b' => 'B']);
            }

            $service->addField('field_' . $i, $field);
        }

        $service->build();

        $this->expectNotToPerformAssertions();
    }

    public function test_choice_fields_get_options_aware_default_validators(): void
    {
        $field = (new FormFieldService(FieldType::SELECT))->label('Pick')->options(['x' => 'X']);
        $service = new FormBuilderService(new PromptsFormBuilder());
        $service->addField('pick', $field);

        // The default validator should accept a valid option and reject others.
        self::assertNull($field->validator?->validate('x'));
        self::assertNotNull($field->validator?->validate('nope'));
    }
}
