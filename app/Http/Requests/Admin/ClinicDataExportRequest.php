<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClinicDataExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && (($user->is_admin ?? false) || $user->role === 'admin');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'reg_from' => ['nullable', 'date'],
            'reg_to' => ['nullable', 'date', 'after_or_equal:reg_from'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'archived'])],
            'record_date_from' => ['nullable', 'date'],
            'record_date_to' => ['nullable', 'date', 'after_or_equal:record_date_from'],
            'record_type' => ['nullable', Rule::in(['consultation', 'followup', 'administration_update'])],
            'doctor_id' => ['nullable', 'integer', Rule::exists('doctors', 'id')],
            'include_private' => ['nullable', 'boolean'],
            'include_attachments' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'department_id.required' => 'Please select a clinic to export.',
            'department_id.exists' => 'The selected clinic is invalid.',
        ];
    }
}
