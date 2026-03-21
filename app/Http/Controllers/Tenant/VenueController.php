<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreVenueRequest;
use App\Http\Requests\Tenant\UpdateVenueRequest;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Venue::class, 'venue');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('tenant.venues.index', [
            'venues' => Venue::query()->latest()->paginate(12),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('tenant.venues.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVenueRequest $request): RedirectResponse
    {
        Venue::query()->create($request->validated());

        return redirect()->route('tenant.venues.index')->with('status', 'Venue created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Venue $venue): View
    {
        return view('tenant.venues.edit', [
            'venue' => $venue,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVenueRequest $request, Venue $venue): RedirectResponse
    {
        $venue->update($request->validated());

        return redirect()->route('tenant.venues.index')->with('status', 'Venue updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venue $venue): RedirectResponse
    {
        $venue->delete();

        return redirect()->route('tenant.venues.index')->with('status', 'Venue deleted successfully.');
    }
}
