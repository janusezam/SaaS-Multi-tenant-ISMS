<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
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
            'sport_id' => ['required', 'integer', 'exists:sports,id'],
            'name' => ['required', 'string', 'max:120'],
            'coach_name' => ['nullable', 'string', 'max:120'],
            'coach_email' => ['nullable', 'email', 'max:255'],
            'division' => ['nullable', 'string', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
