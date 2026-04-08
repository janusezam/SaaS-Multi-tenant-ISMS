<?php

namespace App\Http\Requests\Tenant;

use App\Models\Sport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSportRequest extends FormRequest
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
        /** @var Sport|null $sport */
        $sport = $this->route('sport');

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('sports', 'code')->ignore($sport?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'cover_photo' => ['nullable', 'image', 'max:5120'],
            'remove_cover_photo' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
