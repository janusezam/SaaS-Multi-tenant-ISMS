<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantUserRequest;
use App\Http\Requests\Tenant\UpdateTenantUserRequest;
use App\Mail\TenantUserInviteMail;
use App\Models\TenantUserRegistrationRequest;
use App\Models\User;
use App\Support\TenantPlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderByRaw("FIELD(role, 'university_admin', 'sports_facilitator', 'team_coach', 'student_player')")
            ->orderBy('name')
            ->get();

        $pendingRegistrations = TenantUserRegistrationRequest::query()
            ->where('status', TenantUserRegistrationRequest::STATUS_PENDING)
            ->latest()
            ->get();

        return view('tenant.users.index', [
            'users' => $users,
            'pendingRegistrations' => $pendingRegistrations,
        ]);
    }

    public function create(): View
    {
        return view('tenant.users.create');
    }

    public function store(StoreTenantUserRequest $request): RedirectResponse
    {
        $limitService = TenantPlanLimitService::fromCurrentTenant();

        if (! $limitService->hasCapacity('users', User::query()->count())) {
            return redirect()
                ->route('tenant.users.index')
                ->with('status', $limitService->limitReachedMessage('users'));
        }

        User::query()->create($request->validated());

        return redirect()
            ->route('tenant.users.index')
            ->with('status', 'Tenant user added successfully.');
    }

    public function edit(User $user): View
    {
        return view('tenant.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(UpdateTenantUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if (($validated['password'] ?? null) === null || $validated['password'] === '') {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('tenant.users.index')
            ->with('status', 'Tenant user updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) auth()->id() === (int) $user->id) {
            return redirect()
                ->route('tenant.users.index')
                ->with('status', 'You cannot delete your own account.');
        }

        if ($user->role === 'university_admin') {
            return redirect()
                ->route('tenant.users.index')
                ->with('status', 'University admin accounts cannot be deleted here.');
        }

        $user->delete();

        return redirect()
            ->route('tenant.users.index')
            ->with('status', 'Tenant user removed successfully.');
    }

    public function approveRegistration(TenantUserRegistrationRequest $registration): RedirectResponse
    {
        if ($registration->status !== TenantUserRegistrationRequest::STATUS_PENDING) {
            return redirect()
                ->route('tenant.users.index')
                ->with('status', 'This registration request has already been reviewed.');
        }

        if (User::query()->where('email', $registration->email)->exists()) {
            return redirect()
                ->route('tenant.users.index')
                ->with('status', 'A tenant user with this email already exists.');
        }

        $limitService = TenantPlanLimitService::fromCurrentTenant();

        if (! $limitService->hasCapacity('users', User::query()->count())) {
            return redirect()
                ->route('tenant.users.index')
                ->with('status', $limitService->limitReachedMessage('users'));
        }

        $inviteToken = Str::random(64);

        $attributes = [
            'name' => $registration->name,
            'email' => strtolower((string) $registration->email),
            'phone' => $registration->phone,
            'role' => $registration->role,
            'password' => $registration->password,
            'remember_token' => Str::random(60),
        ];

        if (Schema::hasColumn('users', 'invite_token_hash')) {
            $attributes['invite_token_hash'] = hash('sha256', $inviteToken);
        }

        if (Schema::hasColumn('users', 'invite_expires_at')) {
            $attributes['invite_expires_at'] = now()->addDays(2);
        }

        if (Schema::hasColumn('users', 'invite_sent_at')) {
            $attributes['invite_sent_at'] = now();
        }

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $attributes['email_verified_at'] = null;
        }

        $user = User::query()->create($attributes);

        $activationUrl = route('tenant.user-invite.show', [
            'token' => $inviteToken,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->queue(new TenantUserInviteMail(
            recipientName: (string) $user->name,
            inviteUrl: $activationUrl,
        ));

        $registration->forceFill([
            'status' => TenantUserRegistrationRequest::STATUS_APPROVED,
            'reviewed_by_user_id' => auth()->id(),
            'reviewed_at' => now(),
            'approved_at' => now(),
            'rejected_at' => null,
        ])->save();

        return redirect()
            ->route('tenant.users.index')
            ->with('status', 'Registration approved. Invite link has been sent to the user email.');
    }

    public function destroyRegistration(TenantUserRegistrationRequest $registration): RedirectResponse
    {
        if ($registration->status !== TenantUserRegistrationRequest::STATUS_PENDING) {
            return redirect()
                ->route('tenant.users.index')
                ->with('status', 'Only pending registration requests can be deleted.');
        }

        $registration->delete();

        return redirect()
            ->route('tenant.users.index')
            ->with('status', 'Pending registration request deleted.');
    }

    public function showRegistration(TenantUserRegistrationRequest $registration): View
    {
        abort_unless($registration->status === TenantUserRegistrationRequest::STATUS_PENDING, 404);

        return view('tenant.users.pending-show', [
            'registration' => $registration,
        ]);
    }
}
