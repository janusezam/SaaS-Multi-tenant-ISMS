<x-guest-layout>
    <div class="mb-5">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tenant App</p>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Update Your Password</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Your account is using a temporary password. Set a new password to continue.
        </p>
    </div>

    <form method="POST" action="{{ route('tenant.force-password.update') }}">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="current_password" :value="__('Current Password')" />
            <x-text-input id="current_password" class="mt-1 block w-full" type="password" name="current_password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-end">
            <x-primary-button>
                {{ __('Update Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
