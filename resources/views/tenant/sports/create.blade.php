<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Create Sport</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6">
            <form method="POST" action="{{ route('tenant.sports.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required />
                    @error('name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="code">Code</label>
                    <input id="code" name="code" value="{{ old('code') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required />
                    @error('code')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="description">Description</label>
                    <textarea id="description" name="description" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="cover_photo">Cover Photo</label>
                    <input id="cover_photo" type="file" name="cover_photo" accept="image/*" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-200 file:mr-3 file:rounded-lg file:border file:border-white/15 file:bg-white/10 file:px-3 file:py-2 file:text-xs file:text-slate-100" />
                    @error('cover_photo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-white/10 bg-slate-950/60" />
                    Active
                </label>
                <div class="flex gap-3">
                    <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Save</button>
                    <a href="{{ route('tenant.sports.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
