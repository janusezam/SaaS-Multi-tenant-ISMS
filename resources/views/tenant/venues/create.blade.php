<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Create Venue</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6">
            <form method="POST" action="{{ route('tenant.venues.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required />
                    @error('name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="location">Location</label>
                    <input id="location" name="location" value="{{ old('location') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required />
                    @error('location')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm text-slate-300" for="capacity">Capacity</label>
                        <input id="capacity" type="number" name="capacity" value="{{ old('capacity', 0) }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required />
                        @error('capacity')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm text-slate-300" for="surface_type">Surface Type</label>
                        <input id="surface_type" name="surface_type" value="{{ old('surface_type') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
                        @error('surface_type')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-white/10 bg-slate-950/60" />
                    Active
                </label>
                <div class="flex gap-3">
                    <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Save</button>
                    <a href="{{ route('tenant.venues.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
