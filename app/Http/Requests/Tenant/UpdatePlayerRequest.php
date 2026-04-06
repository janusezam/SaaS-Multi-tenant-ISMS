<?php

namespace App\Http\Requests\Tenant;

use App\Models\Player;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlayerRequest extends FormRequest
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
        /** @var Player|null $player */
        $player = $this->route('player');

        return [
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'player_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'student_id' => ['required', 'string', 'max:40', Rule::unique('players', 'student_id')->ignore($player?->id)],
            'first_name' => ['required_without:player_user_id', 'string', 'max:100'],
            'last_name' => ['required_without:player_user_id', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'position' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
