<?php

namespace App\Http\Requests\Central;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSystemUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('super_admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'version' => ['nullable', 'string', 'max:60'],
            'source' => ['required', 'in:manual,github'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $source = (string) $this->input('source', 'manual');

            if ($source !== 'github') {
                return;
            }

            if ((string) config('services.github.owner') === '' || (string) config('services.github.repo') === '') {
                $validator->errors()->add('source', 'GitHub repo is not configured. Set GITHUB_OWNER and GITHUB_REPO.');
            }

            if ((string) config('services.github.token') === '') {
                $validator->errors()->add('source', 'GitHub token is not configured. Set GITHUB_TOKEN.');
            }
        });
    }
}
