<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSportRequest;
use App\Http\Requests\Tenant\UpdateSportRequest;
use App\Models\Sport;
use App\Support\TenantPlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SportController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Sport::class, 'sport');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('tenant.sports.index', [
            'sports' => Sport::query()->latest()->paginate(12),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('tenant.sports.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSportRequest $request): RedirectResponse
    {
        $limitService = TenantPlanLimitService::fromCurrentTenant();

        if (! $limitService->hasCapacity('sports', Sport::query()->count())) {
            return redirect()
                ->route('tenant.sports.index')
                ->with('status', $limitService->limitReachedMessage('sports'));
        }

        $validated = $request->validated();

        if ($request->hasFile('cover_photo')) {
            $validated['cover_photo_path'] = $request->file('cover_photo')->store(
                'tenants/'.tenant('id').'/sports/covers',
                'public'
            );
        }

        Sport::query()->create($validated);

        return redirect()->route('tenant.sports.index')->with('status', 'Sport created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sport $sport): View
    {
        return view('tenant.sports.show', [
            'sport' => $sport,
            'teams' => $sport->teams()->latest()->get(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function edit(Sport $sport): View
    {
        return view('tenant.sports.edit', [
            'sport' => $sport,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSportRequest $request, Sport $sport): RedirectResponse
    {
        $validated = $request->validated();

        if (($validated['remove_cover_photo'] ?? false) && $sport->cover_photo_path !== null) {
            Storage::disk('public')->delete($sport->cover_photo_path);
            $validated['cover_photo_path'] = null;
        }

        unset($validated['remove_cover_photo']);

        if ($request->hasFile('cover_photo')) {
            if ($sport->cover_photo_path !== null) {
                Storage::disk('public')->delete($sport->cover_photo_path);
            }

            $validated['cover_photo_path'] = $request->file('cover_photo')->store(
                'tenants/'.tenant('id').'/sports/covers',
                'public'
            );
        }

        $sport->update($validated);

        return redirect()->route('tenant.sports.index')->with('status', 'Sport updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sport $sport): RedirectResponse
    {
        if ($sport->cover_photo_path !== null) {
            Storage::disk('public')->delete($sport->cover_photo_path);
        }

        $sport->delete();

        return redirect()->route('tenant.sports.index')->with('status', 'Sport deleted successfully.');
    }
}
