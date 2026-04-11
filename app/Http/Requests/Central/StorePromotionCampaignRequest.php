<?php

namespace App\Http\Requests\Central;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromotionCampaignRequest extends FormRequest
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
        $targetPlanCodes = $this->input('target_plan_codes', []);

        if (! is_array($targetPlanCodes)) {
            $targetPlanCodes = [];
        }

        $this->merge([
            'status' => (string) ($this->input('status') ?: 'draft'),
            'is_stackable_with_coupon' => false,
            'target_plan_codes' => array_values(array_filter(array_map(fn ($code) => trim((string) $code), $targetPlanCodes))),
            'priority' => 100,
            'lifecycle_policy' => (string) ($this->input('lifecycle_policy') ?: 'next_renewal'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'target_plan_codes' => ['nullable', 'array'],
            'target_plan_codes.*' => ['string', Rule::exists('plans', 'code')],
            'lifecycle_policy' => ['required', Rule::in(['next_renewal'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
