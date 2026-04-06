<?php

namespace App\Http\Requests\Central;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyCampaignToRenewalsRequest extends FormRequest
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
            'plan_code' => ['nullable', 'string', Rule::exists('plans', 'code')],
            'billing_cycle' => ['nullable', Rule::in(['monthly', 'yearly'])],
            'status' => ['nullable', Rule::in(['active', 'pending', 'suspended'])],
        ];
    }
}
