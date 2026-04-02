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
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'feature_flags' => ['nullable', 'array'],
            'feature_flags.analytics' => ['nullable', 'boolean'],
            'feature_flags.bracket' => ['nullable', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }
}
