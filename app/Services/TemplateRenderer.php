<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class TemplateRenderer
{
    /**
     * Render a letter from a template.
     *
     * @param DocumentTemplate $template
     * @param Patient $patient
     * @param User $user
     * @param array $extra Placeholder values for text_placeholder blocks
     * @param array $branding Logo/signature data
     * @return string HTML string
     */
    public function renderLetter(
        DocumentTemplate $template,
        Patient $patient,
        User $user,
        array $extra = [],
        array $branding = []
    ): string {
        $builderConfig = $template->builder_config ?? [];
        
        if (empty($builderConfig)) {
            // If no builder config, return existing content or empty
            return $template->content ?? '';
        }

        $html = $this->renderBlocks($builderConfig, $patient, $user, $extra, $branding);
        
        // Wrap in a container with styling
        return $this->wrapLetterHtml($html);
    }

    /**
     * Render blocks recursively.
     *
     * @param array $blocks
     * @param Patient $patient
     * @param User $user
     * @param array $extra
     * @param array $branding
     * @return string
     */
    protected function renderBlocks(
        array $blocks,
        Patient $patient,
        User $user,
        array $extra = [],
        array $branding = []
    ): string {
        $html = '';

        foreach ($blocks as $block) {
            $blockType = $block['type'] ?? null;
            $props = $block['props'] ?? [];
            $children = $block['children'] ?? [];

            switch ($blockType) {
                case 'heading':
                    $html .= $this->renderHeading($props);
                    break;
                    
                case 'paragraph':
                    $html .= $this->renderParagraph($props);
                    break;
                    
                case 'patient_field':
                    $html .= $this->renderPatientField($props, $patient);
                    break;
                    
                case 'doctor_field':
                    $html .= $this->renderDoctorField($props, $user);
                    break;
                    
                case 'date_block':
                    $html .= $this->renderDateBlock($props);
                    break;
                    
                case 'divider':
                    $html .= $this->renderDivider($props);
                    break;
                    
                case 'logo_block':
                    $html .= $this->renderLogoBlock($props, $branding);
                    break;
                    
                case 'signature_block':
                    $html .= $this->renderSignatureBlock($props, $user, $branding);
                    break;
                    
                case 'text_placeholder':
                    $html .= $this->renderTextPlaceholder($props, $extra);
                    break;
                    
                case 'info_text':
                    $html .= $this->renderInfoText($props);
                    break;
            }

            // Render children recursively
            if (!empty($children)) {
                $html .= $this->renderBlocks($children, $patient, $user, $extra, $branding);
            }
        }

        return $html;
    }

    /**
     * Render heading block.
     */
    protected function renderHeading(array $props): string
    {
        $text = $props['text'] ?? '';
        $level = $props['level'] ?? 'h2';
        $align = $props['align'] ?? 'left';
        
        $validLevels = ['h1', 'h2', 'h3'];
        if (!in_array($level, $validLevels)) {
            $level = 'h2';
        }
        
        $style = $align !== 'left' ? " style=\"text-align: {$align};\"" : '';
        
        return "<{$level}{$style}>{$text}</{$level}>";
    }

    /**
     * Render paragraph block.
     */
    protected function renderParagraph(array $props): string
    {
        $html = $props['html'] ?? '';
        
        return "<p>{$html}</p>";
    }

    /**
     * Render patient field block.
     */
    protected function renderPatientField(array $props, Patient $patient): string
    {
        $field = $props['field'] ?? '';
        
        $value = match($field) {
            'name' => $patient->full_name ?? ($patient->first_name . ' ' . $patient->last_name),
            'first_name' => $patient->first_name ?? '',
            'last_name' => $patient->last_name ?? '',
            'dob' => $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d/m/Y') : '',
            'date_of_birth' => $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d/m/Y') : '',
            'age' => $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : '',
            'gender' => ucfirst($patient->gender ?? ''),
            'phone' => $patient->phone ?? '',
            'email' => $patient->email ?? '',
            'address' => $this->formatPatientAddress($patient),
            'id' => $patient->patient_id ?? $patient->id,
            'patient_id' => $patient->patient_id ?? $patient->id,
            'blood_group' => $patient->blood_group ?? '',
            default => $patient->$field ?? '',
        };
        
        return "<span class=\"patient-field patient-field-{$field}\">{$value}</span>";
    }

    /**
     * Render doctor field block.
     */
    protected function renderDoctorField(array $props, User $user): string
    {
        $field = $props['field'] ?? '';
        
        $doctor = $user->doctor ?? null;
        
        $value = match($field) {
            'name' => $user->name ?? ($doctor ? ($doctor->first_name . ' ' . $doctor->last_name) : ''),
            'qualifications' => $doctor->qualification ?? '',
            'clinic_name' => $this->getClinicName($user, $doctor),
            'department' => $this->getDepartmentName($user, $doctor),
            'email' => $user->email ?? '',
            'phone' => $doctor->phone ?? $user->phone ?? '',
            default => '',
        };
        
        return "<span class=\"doctor-field doctor-field-{$field}\">{$value}</span>";
    }

    /**
     * Render date block.
     */
    protected function renderDateBlock(array $props): string
    {
        $format = $props['format'] ?? 'DD/MM/YYYY';
        
        // Convert format to PHP date format
        $phpFormat = str_replace(['DD', 'MM', 'YYYY'], ['d', 'm', 'Y'], $format);
        $date = now()->format($phpFormat);
        
        return "<span class=\"date-block\">{$date}</span>";
    }

    /**
     * Render divider block.
     */
    protected function renderDivider(array $props): string
    {
        $style = $props['style'] ?? 'solid';
        
        $borderStyle = match($style) {
            'dotted' => 'border-bottom: 1px dotted #ccc;',
            'dashed' => 'border-bottom: 1px dashed #ccc;',
            default => 'border-bottom: 1px solid #ccc;',
        };
        
        return "<div style=\"{$borderStyle} margin: 20px 0;\"></div>";
    }

    /**
     * Render logo block.
     */
    protected function renderLogoBlock(array $props, array $branding): string
    {
        $source = $props['source'] ?? 'clinic';
        $customUrl = $props['custom_url'] ?? null;
        $align = $props['align'] ?? 'left';
        $maxWidth = $props['max_width'] ?? '200px';
        
        // Get logo URL
        $logoUrl = $customUrl;
        
        if (!$logoUrl) {
            $logoUrl = match($source) {
                'clinic' => $branding['clinic_logo'] ?? $this->getDefaultLogo(),
                'department' => $branding['department_logo'] ?? $this->getDefaultLogo(),
                'doctor' => $branding['doctor_logo'] ?? $this->getDefaultLogo(),
                default => $this->getDefaultLogo(),
            };
        }
        
        $style = "max-width: {$maxWidth};";
        if ($align !== 'left') {
            $style .= " display: block; margin: 0 auto;";
        }
        
        return "<div style=\"margin: 20px 0; text-align: {$align};\"><img src=\"{$logoUrl}\" alt=\"Logo\" style=\"{$style}\" /></div>";
    }

    /**
     * Render signature block.
     */
    protected function renderSignatureBlock(array $props, User $user, array $branding): string
    {
        $signer = $props['signer'] ?? 'doctor';
        $showName = $props['show_name'] ?? true;
        $showRole = $props['show_role'] ?? true;
        $useSignatureImage = $props['use_signature_image'] ?? false;
        $signatureImageUrl = $props['signature_image_url'] ?? null;
        
        $doctor = $user->doctor ?? null;
        
        $html = '<div class="signature-block" style="margin: 40px 0;">';
        
        // Signature image
        if ($useSignatureImage) {
            $imageUrl = $signatureImageUrl ?? $branding['signature_image'] ?? null;
            
            if ($imageUrl) {
                $html .= "<div style=\"margin-bottom: 20px;\"><img src=\"{$imageUrl}\" alt=\"Signature\" style=\"max-width: 200px;\" /></div>";
            }
        }
        
        // Name and role
        if ($showName || $showRole) {
            $html .= '<div>';
            
            if ($showName) {
                $name = $user->name ?? ($doctor ? ($doctor->first_name . ' ' . $doctor->last_name) : '');
                $html .= "<div style=\"font-weight: bold; margin-bottom: 5px;\">{$name}</div>";
            }
            
            if ($showRole) {
                $role = $doctor ? ($doctor->title ?? 'Dr.') : '';
                $qualification = $doctor ? ($doctor->qualification ?? '') : '';
                $roleText = trim($role . ' ' . $qualification);
                if ($roleText) {
                    $html .= "<div style=\"font-size: 0.9em; color: #666;\">{$roleText}</div>";
                }
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render text placeholder block.
     */
    protected function renderTextPlaceholder(array $props, array $extra): string
    {
        $name = $props['name'] ?? '';
        $label = $props['label'] ?? '';
        
        $value = $extra[$name] ?? $extra[$label] ?? '';
        
        if (empty($value)) {
            // Return placeholder marker if no value provided
            return "<span class=\"text-placeholder\" data-name=\"{$name}\" style=\"color: #999; font-style: italic;\">[{$label}]</span>";
        }
        
        return "<span class=\"text-placeholder text-placeholder-filled\" data-name=\"{$name}\">{$value}</span>";
    }

    /**
     * Render info text block.
     */
    protected function renderInfoText(array $props): string
    {
        $text = $props['text'] ?? '';
        
        return "<p style=\"font-style: italic; color: #666;\">{$text}</p>";
    }

    /**
     * Build form schema from template.
     *
     * @param DocumentTemplate $template
     * @return array
     */
    public function buildFormSchema(DocumentTemplate $template): array
    {
        $builderConfig = $template->builder_config ?? [];
        
        if (empty($builderConfig)) {
            return [];
        }

        $schema = [];
        
        foreach ($builderConfig as $block) {
            $blockType = $block['type'] ?? null;
            
            if ($blockType === 'section') {
                $section = [
                    'title' => $block['props']['title'] ?? '',
                    'description' => $block['props']['description'] ?? '',
                    'fields' => [],
                ];
                
                // Process fields in this section
                $children = $block['children'] ?? [];
                foreach ($children as $fieldBlock) {
                    $field = $this->extractFieldFromBlock($fieldBlock);
                    if ($field) {
                        $section['fields'][] = $field;
                    }
                }
                
                $schema[] = $section;
            }
        }
        
        return $schema;
    }

    /**
     * Extract field configuration from block.
     */
    protected function extractFieldFromBlock(array $block): ?array
    {
        $type = $block['type'] ?? null;
        $props = $block['props'] ?? [];
        
        $fieldTypes = ['text', 'textarea', 'select', 'checkbox', 'checkbox_group', 'radio_group', 'date', 'number', 'info_text'];
        
        if (!in_array($type, $fieldTypes)) {
            return null;
        }
        
        $field = [
            'type' => $type,
            'name' => $props['name'] ?? '',
            'label' => $props['label'] ?? '',
            'required' => $props['required'] ?? false,
        ];
        
        // Type-specific properties
        switch ($type) {
            case 'select':
            case 'checkbox_group':
            case 'radio_group':
                $field['options'] = $props['options'] ?? [];
                break;
                
            case 'number':
                $field['min'] = $props['min'] ?? null;
                $field['max'] = $props['max'] ?? null;
                $field['step'] = $props['step'] ?? 1;
                break;
                
            case 'date':
                $field['format'] = $props['format'] ?? 'YYYY-MM-DD';
                $field['min'] = $props['min'] ?? null;
                $field['max'] = $props['max'] ?? null;
                break;
                
            case 'textarea':
                $field['rows'] = $props['rows'] ?? 4;
                break;
                
            case 'info_text':
                $field['text'] = $props['text'] ?? '';
                break;
        }
        
        return $field;
    }

    /**
     * Wrap letter HTML in container with styling.
     */
    protected function wrapLetterHtml(string $content): string
    {
        $styles = '
            <style>
                .letter-container {
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 40px;
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .letter-container h1, .letter-container h2, .letter-container h3 {
                    margin-top: 20px;
                    margin-bottom: 10px;
                }
                .letter-container p {
                    margin-bottom: 15px;
                }
            </style>
        ';
        
        return $styles . '<div class="letter-container">' . $content . '</div>';
    }

    /**
     * Format patient address.
     */
    protected function formatPatientAddress(Patient $patient): string
    {
        $parts = array_filter([
            $patient->address,
            $patient->city,
            $patient->state,
            $patient->postal_code,
            $patient->country,
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Get clinic name.
     */
    protected function getClinicName(User $user, $doctor): string
    {
        // Try to get from settings or department
        $clinicName = \App\Models\Setting::get('clinic_name');
        
        if (!$clinicName && $doctor) {
            $department = $doctor->primaryDepartment();
            if ($department) {
                return $department->name;
            }
        }
        
        return $clinicName ?? config('app.name', 'Clinic');
    }

    /**
     * Get department name.
     */
    protected function getDepartmentName(User $user, $doctor): string
    {
        if ($doctor) {
            $department = $doctor->primaryDepartment();
            if ($department) {
                return $department->name;
            }
        }
        
        return '';
    }

    /**
     * Get default logo URL.
     */
    protected function getDefaultLogo(): string
    {
        // Use the logo helper if available
        if (function_exists('getLogo')) {
            return getLogo('light');
        }
        
        return asset('assets/images/logo.png');
    }
}

