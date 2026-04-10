<?php

namespace App\Http\Requests\Tenant;

use App\Models\TenantUserRegistrationRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantSelfRegistrationRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                Rule::unique('tenant_user_registration_requests', 'email')->where(fn ($query) => $query->where('status', TenantUserRegistrationRequest::STATUS_PENDING)),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::in(['sports_facilitator', 'team_coach', 'student_player'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
