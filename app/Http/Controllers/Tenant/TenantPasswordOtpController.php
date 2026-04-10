<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\TenantPasswordOtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TenantPasswordOtpController extends Controller
{
    public function create(): View
    {
        return view('tenant.auth.forgot-password-otp');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $key = sprintf('tenant-password-otp:%s|%s', Str::lower((string) $validated['email']), (string) $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many requests. Please try again in a few minutes.',
            ]);
        }

        RateLimiter::hit($key, 600);

        $email = Str::lower((string) $validated['email']);
        $user = User::query()->where('email', $email)->first();

        if ($user !== null) {
            $otpCode = (string) random_int(100000, 999999);

            DB::table('tenant_password_reset_otps')->where('email', $email)->delete();

            DB::table('tenant_password_reset_otps')->insert([
                'email' => $email,
                'otp_hash' => hash('sha256', $otpCode),
                'expires_at' => now()->addMinutes(10),
                'consumed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Mail::to($user->email)->queue(new TenantPasswordOtpMail(
                recipientName: (string) $user->name,
                otpCode: $otpCode,
                expiresInMinutes: 10,
            ));
        }

        return redirect()
            ->route('tenant.password.otp.reset-form', ['email' => $email])
            ->with('status', 'If the email exists in this tenant, an OTP has been sent.');
    }

    public function showResetForm(Request $request): View
    {
        return view('tenant.auth.reset-password-otp', [
            'email' => (string) $request->query('email', ''),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = Str::lower((string) $validated['email']);

        $otpRecord = DB::table('tenant_password_reset_otps')
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->where('expires_at', '>=', now())
            ->orderByDesc('id')
            ->first();

        if ($otpRecord === null || ! hash_equals((string) $otpRecord->otp_hash, hash('sha256', (string) $validated['otp']))) {
            throw ValidationException::withMessages([
                'otp' => 'The provided OTP is invalid or expired.',
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => 'No tenant user found for this email.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make((string) $validated['password']),
            'must_change_password' => false,
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('tenant_password_reset_otps')
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->update([
                'consumed_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->route('tenant.login')->with('status', 'Your password has been reset. Please sign in.');
    }
}
