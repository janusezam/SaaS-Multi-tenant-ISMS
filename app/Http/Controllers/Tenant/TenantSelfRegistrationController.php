<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantSelfRegistrationRequest;
use App\Mail\TenantRegistrationSubmittedMail;
use App\Models\TenantUserRegistrationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class TenantSelfRegistrationController extends Controller
{
    public function create(): View
    {
        return view('tenant.auth.register');
    }

    public function store(StoreTenantSelfRegistrationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $normalizedRole = User::normalizeTenantRole((string) $validated['role']);

        $registration = TenantUserRegistrationRequest::query()->create([
            'name' => $validated['name'],
            'email' => strtolower((string) $validated['email']),
            'phone' => $validated['phone'],
            'role' => $normalizedRole,
            'password' => Hash::make((string) $validated['password']),
            'status' => TenantUserRegistrationRequest::STATUS_PENDING,
        ]);

        $tenantAdmins = User::query()
            ->where('role', 'university_admin')
            ->get();

        foreach ($tenantAdmins as $admin) {
            Mail::to($admin->email)->queue(new TenantRegistrationSubmittedMail(
                adminName: (string) $admin->name,
                requesterName: (string) $registration->name,
                requesterEmail: (string) $registration->email,
                requesterRole: (string) $registration->role,
            ));
        }

        return redirect()
            ->route('tenant.login')
            ->with('status', 'Registration submitted. A tenant admin will review your account request.');
    }
}
