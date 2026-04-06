<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlayerRequest extends FormRequest
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
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'player_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'student_id' => ['required', 'string', 'max:40', 'unique:players,student_id'],
            'first_name' => ['required_without:player_user_id', 'string', 'max:100'],
            'last_name' => ['required_without:player_user_id', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'position' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
