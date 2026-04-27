<?php

namespace App\Providers;

use App\Models\BracketMatch;
use App\Models\BracketMatchAudit;
use App\Models\Game;
use App\Models\GameResultAudit;
use App\Models\Player;
use App\Models\Sport;
use App\Models\Team;
use App\Models\Venue;
use App\Policies\BracketMatchAuditPolicy;
use App\Policies\BracketMatchPolicy;
use App\Policies\GamePolicy;
use App\Policies\GameResultAuditPolicy;
use App\Policies\PlayerPolicy;
use App\Policies\SportPolicy;
use App\Policies\TeamPolicy;
use App\Policies\VenuePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Sport::class, SportPolicy::class);
        Gate::policy(Venue::class, VenuePolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Player::class, PlayerPolicy::class);
        Gate::policy(Game::class, GamePolicy::class);
        Gate::policy(GameResultAudit::class, GameResultAuditPolicy::class);
        Gate::policy(BracketMatch::class, BracketMatchPolicy::class);
        Gate::policy(BracketMatchAudit::class, BracketMatchAuditPolicy::class);
    }
}
