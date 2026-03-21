<?php

namespace App\Http\Requests\Tenant;

use App\Models\BracketMatch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBracketResultRequest extends FormRequest
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
        /** @var BracketMatch|null $match */
        $match = $this->route('match');

        $allowedWinnerIds = array_values(array_filter([
            $match?->home_team_id,
            $match?->away_team_id,
        ]));

        return [
            'winner_team_id' => [
                'required',
                'integer',
                Rule::in($allowedWinnerIds),
            ],
        ];
    }
}
