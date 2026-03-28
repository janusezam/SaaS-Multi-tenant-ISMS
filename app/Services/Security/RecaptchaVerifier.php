<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;

class RecaptchaVerifier
{
    public function isEnabled(): bool
    {
        if (app()->environment('testing') && ! (bool) config('services.recaptcha.force_in_tests', false)) {
            return false;
        }

        return filled((string) config('services.recaptcha.site_key'))
            && filled((string) config('services.recaptcha.secret_key'));
    }

    public function verify(?string $token, ?string $ipAddress = null, ?string $expectedAction = null): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        $response = Http::asForm()
            ->timeout(6)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => (string) config('services.recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => $ipAddress,
            ]);

        if (! $response->ok()) {
            return false;
        }

        $payload = $response->json();

        if (! is_array($payload) || ! ($payload['success'] ?? false)) {
            return false;
        }

        if ((string) config('services.recaptcha.version', 'v3') === 'v3') {
            $score = (float) ($payload['score'] ?? 0.0);
            $minimumScore = (float) config('services.recaptcha.minimum_score', 0.5);

            if ($score < $minimumScore) {
                return false;
            }

            if ($expectedAction !== null && ($payload['action'] ?? null) !== $expectedAction) {
                return false;
            }
        }

        return true;
    }
}
