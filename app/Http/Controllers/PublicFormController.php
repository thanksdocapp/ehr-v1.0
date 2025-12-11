<?php

namespace App\Http\Controllers;

use App\Models\FormRequest;
use App\Mail\FormSubmissionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PublicFormController extends Controller
{
    /**
     * Display the form for filling.
     */
    public function show(string $token)
    {
        $formRequest = FormRequest::where('token', $token)
            ->with(['template', 'patient', 'requester', 'patientDocument'])
            ->firstOrFail();

        // Mark as opened if pending (but don't prevent filling if already opened)
        if ($formRequest->isPending()) {
            $formRequest->markAsOpened();
        }

        // Check if form can be filled (after marking as opened if needed)
        // Forms can be filled if status is PENDING or OPENED (not COMPLETED or EXPIRED)
        if (!$formRequest->canBeFilled()) {
            if ($formRequest->isCompleted()) {
                return view('forms.already-completed', compact('formRequest'));
            }
            if ($formRequest->isExpired()) {
                return view('forms.expired', compact('formRequest'));
            }
        }

        // Get the content to parse - use rendered_content if available, otherwise from template
        $content = $formRequest->rendered_content;
        if (empty($content) && $formRequest->template) {
            $content = $formRequest->template->content ?? '';
        }

        // Parse the template content to extract form fields
        $formFields = $this->extractFormFields($content);

        return view('forms.fill', compact('formRequest', 'formFields'));
    }

    /**
     * Submit the filled form.
     */
    public function submit(Request $request, string $token)
    {
        $formRequest = FormRequest::where('token', $token)
            ->with(['template', 'patient', 'requester', 'generatedDocument'])
            ->firstOrFail();

        // Check if form can be filled
        if (!$formRequest->canBeFilled()) {
            return redirect()->route('forms.fill', $token)
                ->with('error', 'This form can no longer be submitted.');
        }

        // Get the content to parse - use rendered_content if available, otherwise from template
        $content = $formRequest->rendered_content;
        if (empty($content) && $formRequest->template) {
            $content = $formRequest->template->content ?? '';
        }

        // Validate the submitted data
        $formFields = $this->extractFormFields($content);
        $rules = $this->buildValidationRules($formFields);

        $validated = $request->validate($rules);

        // Convert checkbox values to Yes/No for better readability
        foreach ($formFields as $field) {
            if ($field['type'] === 'checkbox') {
                $fieldName = $field['name'];
                $validated[$fieldName] = !empty($validated[$fieldName]) ? 'Yes' : 'No';
            }
        }

        // Store the form data
        $formRequest->markAsCompleted($validated);

        // Send notification to the requester
        try {
            Mail::to($formRequest->requester->email)
                ->send(new FormSubmissionNotification($formRequest));
        } catch (\Exception $e) {
            // Log but don't fail if email fails
            Log::error('Failed to send form submission notification: ' . $e->getMessage());
        }

        return view('forms.thank-you', compact('formRequest'));
    }

    /**
     * Save partial form data (Complete for now).
     */
    public function savePartial(Request $request, string $token)
    {
        $formRequest = FormRequest::where('token', $token)
            ->with(['template', 'patient'])
            ->firstOrFail();

        // Check if form can be filled
        if (!$formRequest->canBeFilled()) {
            return redirect()->route('forms.fill', $token)
                ->with('error', 'This form can no longer be modified.');
        }

        // Get all form data without strict validation (partial save)
        $formData = $request->except(['_token']);

        // Save partial data
        $formRequest->savePartialData($formData);

        return view('forms.saved', compact('formRequest'));
    }

    /**
     * Extract form fields from template content.
     * Looks for patterns like {{input:field_name:label:type}} or {{textarea:field_name:label}}
     */
    protected function extractFormFields(string $content): array
    {
        $fields = [];

        // Match input fields: {{input:field_name:Label Text:type}}
        // type can be: text, email, tel, date, number, etc.
        preg_match_all('/\{\{input:([a-z_]+):([^:}]+)(?::([a-z]+))?\}\}/i', $content, $inputMatches, PREG_SET_ORDER);
        foreach ($inputMatches as $match) {
            $fields[] = [
                'type' => 'input',
                'input_type' => $match[3] ?? 'text',
                'name' => $match[1],
                'label' => $match[2],
                'required' => true,
            ];
        }

        // Match textarea fields: {{textarea:field_name:Label Text}}
        preg_match_all('/\{\{textarea:([a-z_]+):([^}]+)\}\}/i', $content, $textareaMatches, PREG_SET_ORDER);
        foreach ($textareaMatches as $match) {
            $fields[] = [
                'type' => 'textarea',
                'name' => $match[1],
                'label' => $match[2],
                'required' => true,
            ];
        }

        // Match select fields: {{select:field_name:Label Text:option1,option2,option3}}
        preg_match_all('/\{\{select:([a-z_]+):([^:]+):([^}]+)\}\}/i', $content, $selectMatches, PREG_SET_ORDER);
        foreach ($selectMatches as $match) {
            $fields[] = [
                'type' => 'select',
                'name' => $match[1],
                'label' => $match[2],
                'options' => explode(',', $match[3]),
                'required' => true,
            ];
        }

        // Match checkbox fields: {{checkbox:field_name:Label Text}}
        preg_match_all('/\{\{checkbox:([a-z_]+):([^}]+)\}\}/i', $content, $checkboxMatches, PREG_SET_ORDER);
        foreach ($checkboxMatches as $match) {
            $fields[] = [
                'type' => 'checkbox',
                'name' => $match[1],
                'label' => $match[2],
                'required' => false,
            ];
        }

        // Match radio fields: {{radio:field_name:Label Text:option1,option2,option3}}
        preg_match_all('/\{\{radio:([a-z_]+):([^:]+):([^}]+)\}\}/i', $content, $radioMatches, PREG_SET_ORDER);
        foreach ($radioMatches as $match) {
            $fields[] = [
                'type' => 'radio',
                'name' => $match[1],
                'label' => $match[2],
                'options' => explode(',', $match[3]),
                'required' => true,
            ];
        }

        // Match signature field: {{signature:field_name:Label Text}}
        preg_match_all('/\{\{signature:([a-z_]+):([^}]+)\}\}/i', $content, $signatureMatches, PREG_SET_ORDER);
        foreach ($signatureMatches as $match) {
            $fields[] = [
                'type' => 'signature',
                'name' => $match[1],
                'label' => $match[2],
                'required' => true,
            ];
        }

        return $fields;
    }

    /**
     * Build validation rules for form fields.
     */
    protected function buildValidationRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = [];

            if ($field['required']) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field['type']) {
                case 'input':
                    if ($field['input_type'] === 'email') {
                        $fieldRules[] = 'email';
                    } elseif ($field['input_type'] === 'number') {
                        $fieldRules[] = 'numeric';
                    } elseif ($field['input_type'] === 'date') {
                        $fieldRules[] = 'date';
                    } elseif ($field['input_type'] === 'tel') {
                        $fieldRules[] = 'string';
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
                    $fieldRules[] = 'in:' . implode(',', $field['options']);
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
                case 'signature':
                    $fieldRules[] = 'string'; // Base64 encoded signature data
                    break;
            }

            $rules[$field['name']] = implode('|', $fieldRules);
        }

        return $rules;
    }
}
