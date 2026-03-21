<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePlayerRequest;
use App\Http\Requests\Tenant\UpdatePlayerRequest;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Player::class, 'player');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('tenant.players.index', [
            'players' => Player::query()->with('team')->latest()->paginate(12),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('tenant.players.create', [
            'teams' => Team::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlayerRequest $request): RedirectResponse
    {
        Player::query()->create($request->validated());

        return redirect()->route('tenant.players.index')->with('status', 'Player created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Player $player): View
    {
        return view('tenant.players.edit', [
            'player' => $player,
            'teams' => Team::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlayerRequest $request, Player $player): RedirectResponse
    {
        $player->update($request->validated());

        return redirect()->route('tenant.players.index')->with('status', 'Player updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Player $player): RedirectResponse
    {
        $player->delete();

        return redirect()->route('tenant.players.index')->with('status', 'Player deleted successfully.');
    }
}
