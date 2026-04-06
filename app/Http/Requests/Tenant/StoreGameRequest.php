<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGameRequest extends FormRequest
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
        $sportId = $this->input('sport_id');

        return [
            'sport_id' => ['required', 'integer', 'exists:sports,id'],
            'home_team_id' => [
                'required',
                'integer',
                Rule::exists('teams', 'id')->where(fn ($query) => $query->where('sport_id', $sportId)),
                'different:away_team_id',
            ],
            'away_team_id' => [
                'required',
                'integer',
                Rule::exists('teams', 'id')->where(fn ($query) => $query->where('sport_id', $sportId)),
                'different:home_team_id',
            ],
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
            'scheduled_at' => ['required', 'date'],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled'])],
            'home_score' => ['nullable', 'integer', 'min:0'],
            'away_score' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
