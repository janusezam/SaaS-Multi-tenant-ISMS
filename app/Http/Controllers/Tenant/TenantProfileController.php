<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateTenantProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantProfileController extends Controller
{
    public function edit(): View
    {
        return view('tenant.profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateTenantProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        if (($validated['remove_profile_photo'] ?? false) && $user->profile_photo_path !== null) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->profile_photo_path = null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path !== null) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = $request->file('profile_photo')->store(
                'tenants/'.tenant('id').'/profiles',
                'public'
            );
        }

        $user->save();

        return redirect()
            ->route('tenant.profile.edit')
            ->with('status', 'Tenant profile updated successfully.');
    }
}
