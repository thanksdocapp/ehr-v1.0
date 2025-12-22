<?php

namespace App\Services;

/**
 * Formeo JSON Schema to HTML Renderer
 * 
 * Converts Formeo form schema JSON into HTML form elements
 * for rendering in Blade templates.
 */
class FormeoRenderer
{
    /**
     * Render Formeo schema as HTML form fields
     * 
     * @param array|null $schema Formeo schema (JSON decoded)
     * @param array $savedData Previously saved form data (for editing)
     * @return array Array of form field definitions compatible with existing form rendering
     */
    public function renderFormFields(?array $schema, array $savedData = []): array
    {
        if (empty($schema) || !isset($schema['rows'])) {
            return [];
        }

        $fields = [];

        foreach ($schema['rows'] as $row) {
            if (!isset($row['columns'])) {
                continue;
            }

            foreach ($row['columns'] as $column) {
                if (!isset($column['fields'])) {
                    continue;
                }

                foreach ($column['fields'] as $field) {
                    $renderedField = $this->renderField($field, $savedData);
                    if ($renderedField) {
                        $fields[] = $renderedField;
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Render a single Formeo field
     * 
     * @param array $field Formeo field definition
     * @param array $savedData Previously saved form data
     * @return array|null Field definition compatible with existing form system
     */
    protected function renderField(array $field, array $savedData = []): ?array
    {
        $fieldType = $field['type'] ?? null;
        if (!$fieldType) {
            return null;
        }

        $label = $field['label'] ?? '';
        $name = $field['name'] ?? $field['id'] ?? null;
        $required = $field['required'] ?? false;
        $placeholder = $field['placeholder'] ?? '';
        $description = $field['description'] ?? '';

        // Map Formeo field types to our system's field types
        $fieldMapping = [
            'text' => 'input',
            'textarea' => 'textarea',
            'select' => 'select',
            'radio-group' => 'radio',
            'checkbox-group' => 'checkbox',
            'checkbox' => 'checkbox',
            'number' => 'input',
            'email' => 'input',
            'date' => 'input',
            'file' => 'file',
        ];

        $mappedType = $fieldMapping[$fieldType] ?? $fieldType;

        $renderedField = [
            'type' => $mappedType,
            'name' => $name,
            'label' => $label,
            'required' => $required,
            'placeholder' => $placeholder,
            'description' => $description,
        ];

        // Handle input type specifics
        if ($mappedType === 'input') {
            $renderedField['input_type'] = $this->getInputType($fieldType);
        }

        // Handle select/radio options
        if (in_array($mappedType, ['select', 'radio'])) {
            $renderedField['options'] = $this->extractOptions($field);
        }

        // Handle checkbox group (treat as individual checkboxes)
        if ($fieldType === 'checkbox-group') {
            $options = $this->extractOptions($field);
            // For checkbox groups, we'll render multiple checkboxes
            // For now, return as radio for compatibility
            $renderedField['type'] = 'checkbox';
            $renderedField['options'] = $options;
        }

        return $renderedField;
    }

    /**
     * Get HTML input type from Formeo field type
     * 
     * @param string $fieldType Formeo field type
     * @return string HTML input type
     */
    protected function getInputType(string $fieldType): string
    {
        $typeMap = [
            'text' => 'text',
            'email' => 'email',
            'number' => 'number',
            'date' => 'date',
            'tel' => 'tel',
            'url' => 'url',
        ];

        return $typeMap[$fieldType] ?? 'text';
    }

    /**
     * Extract options from Formeo field
     * 
     * @param array $field Formeo field definition
     * @return array Array of option values
     */
    protected function extractOptions(array $field): array
    {
        $options = [];

        // Check for options in various formats
        if (isset($field['values']) && is_array($field['values'])) {
            foreach ($field['values'] as $option) {
                if (is_string($option)) {
                    $options[] = $option;
                } elseif (is_array($option) && isset($option['label'])) {
                    $options[] = $option['label'];
                } elseif (is_array($option) && isset($option['value'])) {
                    $options[] = $option['value'];
                }
            }
        }

        // Also check for options array
        if (isset($field['options']) && is_array($field['options'])) {
            foreach ($field['options'] as $option) {
                if (is_string($option)) {
                    $options[] = $option;
                } elseif (is_array($option) && isset($option['label'])) {
                    $options[] = $option['label'];
                }
            }
        }

        return array_unique($options);
    }

    /**
     * Build validation rules from Formeo schema
     * 
     * @param array|null $schema Formeo schema
     * @return array Laravel validation rules
     */
    public function buildValidationRules(?array $schema): array
    {
        $rules = [];
        $fields = $this->renderFormFields($schema);

        foreach ($fields as $field) {
            $fieldRules = [];

            if ($field['required']) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field['type']) {
                case 'input':
                    $inputType = $field['input_type'] ?? 'text';
                    if ($inputType === 'email') {
                        $fieldRules[] = 'email';
                    } elseif ($inputType === 'number') {
                        $fieldRules[] = 'numeric';
                    } elseif ($inputType === 'date') {
                        $fieldRules[] = 'date';
                    } else {
                        $fieldRules[] = 'string';
                        $fieldRules[] = 'max:255';
                    }
                    break;

                case 'textarea':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:5000';
                    break;

                case 'select':
                case 'radio':
                    if (!empty($field['options'])) {
                        $fieldRules[] = 'in:' . implode(',', array_map('trim', $field['options']));
                    }
                    break;

                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;

                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:10240'; // 10MB default
                    break;
            }

            $rules[$field['name']] = implode('|', $fieldRules);
        }

        return $rules;
    }
}
