<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSportRequest;
use App\Http\Requests\Tenant\UpdateSportRequest;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
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
        Sport::query()->create($request->validated());

        return redirect()->route('tenant.sports.index')->with('status', 'Sport created successfully.');
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
        $sport->update($request->validated());

        return redirect()->route('tenant.sports.index')->with('status', 'Sport updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sport $sport): RedirectResponse
    {
        $sport->delete();

        return redirect()->route('tenant.sports.index')->with('status', 'Sport deleted successfully.');
    }
}
