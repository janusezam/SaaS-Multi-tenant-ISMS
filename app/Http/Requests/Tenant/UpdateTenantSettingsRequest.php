<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $isUniversityAdmin = $this->user()?->hasTenantRole('university_admin') === true;
        $settingsSection = (string) $this->input('settings_section', 'all');
        $updatingTheme = in_array($settingsSection, ['theme_brand', 'all'], true);

        return [
            'settings_section' => ['nullable', 'in:theme_brand,branding,all'],
            'brand_primary_color' => [$updatingTheme ? 'required' : 'nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_secondary_color' => [$updatingTheme ? 'required' : 'nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_preference' => [$updatingTheme ? 'required' : 'nullable', 'in:system,light,dark'],
            'use_custom_theme' => ['sometimes', 'boolean'],
            'branding_logo' => ['nullable', 'image', 'max:4096', Rule::prohibitedIf(! $isUniversityAdmin)],
            'remove_branding_logo' => ['nullable', 'boolean', Rule::prohibitedIf(! $isUniversityAdmin)],
            'login_brand_badge' => ['nullable', 'string', 'max:120', Rule::prohibitedIf(! $isUniversityAdmin)],
            'login_brand_heading' => ['nullable', 'string', 'max:160', Rule::prohibitedIf(! $isUniversityAdmin)],
            'login_brand_description' => ['nullable', 'string', 'max:500', Rule::prohibitedIf(! $isUniversityAdmin)],
            'login_brand_feature_1' => ['nullable', 'string', 'max:160', Rule::prohibitedIf(! $isUniversityAdmin)],
            'login_brand_feature_2' => ['nullable', 'string', 'max:160', Rule::prohibitedIf(! $isUniversityAdmin)],
            'login_brand_feature_3' => ['nullable', 'string', 'max:160', Rule::prohibitedIf(! $isUniversityAdmin)],
        ];
    }
}
