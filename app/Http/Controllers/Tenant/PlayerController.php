<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePlayerRequest;
use App\Http\Requests\Tenant\UpdatePlayerRequest;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
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
            'playerUsers' => User::query()->where('role', 'student_player')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlayerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $playerUserId = $validated['player_user_id'] ?? null;
        unset($validated['player_user_id']);

        if ($playerUserId !== null) {
            $playerUser = User::query()
                ->where('id', $playerUserId)
                ->where('role', 'student_player')
                ->first();

            if ($playerUser !== null) {
                [$firstName, $lastName] = $this->splitName($playerUser->name);

                $validated['first_name'] = $firstName;
                $validated['last_name'] = $lastName;
                $validated['email'] = $playerUser->email;
            }
        }

        Player::query()->create($validated);

        return redirect()->route('tenant.players.index')->with('status', 'Player created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Player $player): View
    {
        $selectedPlayerUserId = User::query()
            ->where('role', 'student_player')
            ->where('email', $player->email)
            ->value('id');

        return view('tenant.players.edit', [
            'player' => $player,
            'teams' => Team::query()->where('is_active', true)->orderBy('name')->get(),
            'playerUsers' => User::query()->where('role', 'student_player')->orderBy('name')->get(),
            'selectedPlayerUserId' => $selectedPlayerUserId,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlayerRequest $request, Player $player): RedirectResponse
    {
        $validated = $request->validated();

        $playerUserId = $validated['player_user_id'] ?? null;
        unset($validated['player_user_id']);

        if ($playerUserId !== null) {
            $playerUser = User::query()
                ->where('id', $playerUserId)
                ->where('role', 'student_player')
                ->first();

            if ($playerUser !== null) {
                [$firstName, $lastName] = $this->splitName($playerUser->name);

                $validated['first_name'] = $firstName;
                $validated['last_name'] = $lastName;
                $validated['email'] = $playerUser->email;
            }
        }

        $player->update($validated);

        return redirect()->route('tenant.players.index')->with('status', 'Player updated successfully.');
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitName(string $fullName): array
    {
        $name = trim($fullName);

        if ($name === '') {
            return ['Student', 'Player'];
        }

        $parts = preg_split('/\s+/', $name);

        if ($parts === false || count($parts) === 0) {
            return [$name, 'Player'];
        }

        $firstName = (string) array_shift($parts);
        $lastName = trim(implode(' ', $parts));

        if ($lastName === '') {
            $lastName = 'Player';
        }

        return [$firstName, $lastName];
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
