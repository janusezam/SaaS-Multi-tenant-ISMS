<?php

namespace App\Http\Requests\Central\Auth;

use App\Services\Security\RecaptchaVerifier;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SuperAdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];

        if (app(RecaptchaVerifier::class)->isEnabled()) {
            if ((string) config('services.recaptcha.version', 'v3') === 'v2') {
                $rules['g-recaptcha-response'] = ['required', 'string'];
            } else {
                $rules['recaptcha_token'] = ['required', 'string'];
            }
        }

        return $rules;
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureRecaptchaIsValid();

        $this->ensureIsNotRateLimited();

        if (! Auth::guard('super_admin')->attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @throws ValidationException
     */
    public function ensureRecaptchaIsValid(): void
    {
        $verifier = app(RecaptchaVerifier::class);

        if (! $verifier->isEnabled()) {
            return;
        }

        $version = (string) config('services.recaptcha.version', 'v3');
        $tokenField = $version === 'v2' ? 'g-recaptcha-response' : 'recaptcha_token';
        $action = $version === 'v3' ? 'central_login' : null;

        if (! $verifier->verify($this->string($tokenField)->toString(), $this->ip(), $action)) {
            throw ValidationException::withMessages([
                $tokenField => 'reCAPTCHA verification failed. Please try again.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
