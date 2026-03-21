<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        return match ($user?->role) {
            'university_admin' => view('tenant.dashboards.university-admin'),
            'sports_facilitator' => view('tenant.dashboards.sports-facilitator'),
            'team_coach' => view('tenant.dashboards.team-coach'),
            'student_player' => view('tenant.dashboards.student-player'),
            default => abort(403, 'Unknown tenant role.'),
        };
    }
}
