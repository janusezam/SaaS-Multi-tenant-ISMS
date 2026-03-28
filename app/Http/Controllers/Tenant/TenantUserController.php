<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantUserRequest;
use App\Http\Requests\Tenant\UpdateTenantUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderByRaw("FIELD(role, 'university_admin', 'sports_facilitator', 'team_coach', 'student_player')")
            ->orderBy('name')
            ->get();

        return view('tenant.users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('tenant.users.create');
    }

    public function store(StoreTenantUserRequest $request): RedirectResponse
    {
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
}
