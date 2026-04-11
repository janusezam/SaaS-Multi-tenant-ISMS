<?php

namespace App\Http\Requests\Central;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePublicSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        $subdomain = Str::of((string) $this->input('subdomain'))
            ->lower()
            ->trim()
            ->value();

        $this->merge([
            'subdomain' => $subdomain,
            'tenant_domain' => $subdomain !== '' ? $subdomain.'.'.$this->tenantBaseDomain() : null,
        ]);
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
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn($this->reservedSubdomains()),
                'unique:tenants,id',
            ],
            'plan' => ['required', Rule::exists('plans', 'code')->where('is_active', true)],
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'tenant_domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subdomain.regex' => 'Subdomain may only contain lowercase letters, numbers, and hyphens.',
            'subdomain.not_in' => 'This subdomain is reserved and cannot be used.',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function reservedSubdomains(): array
    {
        return [
            'www',
            'admin',
            'central',
            'api',
            'app',
            'mail',
        ];
    }

    private function tenantBaseDomain(): string
    {
        $centralDomains = config('tenancy.central_domains', ['localhost']);

        foreach ($centralDomains as $domain) {
            if (is_string($domain) && ! filter_var($domain, FILTER_VALIDATE_IP)) {
                return Str::of($domain)->startsWith('central.')
                    ? (string) Str::after($domain, 'central.')
                    : $domain;
            }
        }

        return 'localhost';
    }
}
