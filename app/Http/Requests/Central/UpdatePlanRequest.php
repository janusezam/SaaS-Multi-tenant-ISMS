<?php

namespace App\Http\Requests\Central;

use App\Models\Plan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $featureFlags = $this->input('feature_flags', []);

        if (! is_array($featureFlags)) {
            $featureFlags = [];
        }

        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'is_featured' => $this->boolean('is_featured', false),
            'max_users' => $this->normalizeNullableInteger($this->input('max_users')),
            'max_teams' => $this->normalizeNullableInteger($this->input('max_teams')),
            'max_sports' => $this->normalizeNullableInteger($this->input('max_sports')),
            'feature_flags' => [
                'analytics' => filter_var($featureFlags['analytics'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'bracket' => filter_var($featureFlags['bracket'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ],
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Plan|null $plan */
        $plan = $this->route('plan');

        return [
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('plans', 'code')->ignore($plan?->id)],
            'name' => ['required', 'string', 'max:80'],
            'marketing_tagline' => ['nullable', 'string', 'max:160'],
            'badge_label' => ['nullable', 'string', 'max:40'],
            'cta_label' => ['nullable', 'string', 'max:40'],
            'marketing_points' => ['nullable', 'string', 'max:1200'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'max_teams' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'max_sports' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'feature_flags' => ['nullable', 'array'],
            'feature_flags.analytics' => ['nullable', 'boolean'],
            'feature_flags.bracket' => ['nullable', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }

    private function normalizeNullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
