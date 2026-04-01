<?php

namespace App\Http\Requests\Central;

use App\Models\University;
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
            'plan' => ['required', Rule::exists('plans', 'code')->where('is_active', true)],
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'subscription_starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Approved schools cannot be set back to pending.',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function allowedStatuses(): array
    {
        $university = $this->route('university');

        if (! $university instanceof University) {
            return ['pending', 'active', 'expired'];
        }

        if ($university->status === 'pending') {
            return ['pending', 'active', 'expired'];
        }

        return ['active', 'expired'];
    }
}
