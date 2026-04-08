<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand_primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_preference' => ['required', 'in:system,light,dark'],
            'privacy_message' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
