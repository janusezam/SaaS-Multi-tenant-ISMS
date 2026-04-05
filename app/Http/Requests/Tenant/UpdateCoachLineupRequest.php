<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCoachLineupRequest extends FormRequest
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
            'player_ids' => ['required', 'array', 'min:1'],
            'player_ids.*' => ['integer', 'exists:players,id'],
            'starter_player_ids' => ['nullable', 'array'],
            'starter_player_ids.*' => ['integer', 'exists:players,id'],
            'coach_note' => ['nullable', 'string', 'max:255'],
            'confirm_team' => ['nullable', 'boolean'],
        ];
    }
}
