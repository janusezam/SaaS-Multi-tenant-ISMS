<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForcePasswordController extends Controller
{
    public function edit(): View
    {
        return view('tenant.auth.force-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $attributes = [
            'password' => Hash::make($validated['password']),
        ];

        if (Schema::hasColumn('users', 'must_change_password')) {
            $attributes['must_change_password'] = false;
        }

        $request->user()->update($attributes);

        return redirect()->route('tenant.dashboard')->with('status', 'password-updated');
    }
}
