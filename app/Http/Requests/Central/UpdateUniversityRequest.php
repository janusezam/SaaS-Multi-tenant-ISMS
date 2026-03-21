<?php

namespace App\Http\Requests\Central;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUniversityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'school_address' => ['required', 'string', 'max:255'],
            'tenant_admin_name' => ['required', 'string', 'max:255'],
            'tenant_admin_email' => ['required', 'string', 'email', 'max:255'],
            'plan' => ['required', Rule::in(['basic', 'pro'])],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'subscription_starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}
