<div>
    <label class="mb-2 block text-sm text-slate-300" for="sport_id">Sport</label>
    <select id="sport_id" name="sport_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required>
        <option value="">Select sport</option>
        @foreach ($sports as $sport)
            <option value="{{ $sport->id }}" @selected(old('sport_id', $game->sport_id ?? null) == $sport->id)>{{ $sport->name }}</option>
        @endforeach
    </select>
    @error('sport_id')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm text-slate-300" for="home_team_id">Home Team</label>
        <select id="home_team_id" name="home_team_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required>
            <option value="">Select home team</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected(old('home_team_id', $game->home_team_id ?? null) == $team->id)>{{ $team->name }}</option>
            @endforeach
        </select>
        @error('home_team_id')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mb-2 block text-sm text-slate-300" for="away_team_id">Away Team</label>
        <select id="away_team_id" name="away_team_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required>
            <option value="">Select away team</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected(old('away_team_id', $game->away_team_id ?? null) == $team->id)>{{ $team->name }}</option>
            @endforeach
        </select>
        @error('away_team_id')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm text-slate-300" for="venue_id">Venue</label>
        <select id="venue_id" name="venue_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required>
            <option value="">Select venue</option>
            @foreach ($venues as $venue)
                <option value="{{ $venue->id }}" @selected(old('venue_id', $game->venue_id ?? null) == $venue->id)>{{ $venue->name }}</option>
            @endforeach
        </select>
        @error('venue_id')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mb-2 block text-sm text-slate-300" for="scheduled_at">Scheduled At</label>
        <input id="scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', isset($game) ? $game->scheduled_at->format('Y-m-d\\TH:i') : '') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required />
        @error('scheduled_at')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label class="mb-2 block text-sm text-slate-300" for="status">Status</label>
        <select id="status" name="status" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required>
            @foreach (['scheduled', 'completed', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(old('status', $game->status ?? 'scheduled') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mb-2 block text-sm text-slate-300" for="home_score">Home Score</label>
        <input id="home_score" type="number" min="0" name="home_score" value="{{ old('home_score', $game->home_score ?? '') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
        @error('home_score')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mb-2 block text-sm text-slate-300" for="away_score">Away Score</label>
        <input id="away_score" type="number" min="0" name="away_score" value="{{ old('away_score', $game->away_score ?? '') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
        @error('away_score')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
    </div>
</div>
