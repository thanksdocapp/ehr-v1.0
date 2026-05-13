<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->user()->role === 'doctor') {
            $rules['specialization'] = ['nullable', 'string', 'max:255'];
            $httpsBookingUrlRule = function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || $value === '') {
                    return;
                }
                if (! is_string($value)) {
                    $fail('The :attribute must be a valid URL.');

                    return;
                }
                if (! filter_var($value, FILTER_VALIDATE_URL)) {
                    $fail('The :attribute must be a valid URL.');

                    return;
                }
                $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
                if (! in_array($scheme, ['http', 'https'], true)) {
                    $fail('The :attribute must use http or https.');

                    return;
                }
                if (app()->environment('production') && $scheme !== 'https') {
                    $fail('The :attribute must use HTTPS in production.');
                }
            };
            $rules['post_booking_redirect_url'] = ['nullable', 'string', 'max:2048', $httpsBookingUrlRule];
            $rules['clinic_post_booking_redirect_url'] = ['nullable', 'string', 'max:2048', $httpsBookingUrlRule];
        }

        return $rules;
    }
}
