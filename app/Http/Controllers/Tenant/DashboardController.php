<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user?->hasTenantRole('university_admin')) {
            return view('tenant.dashboards.university-admin');
        }

        if ($user?->hasTenantRole('sports_facilitator')) {
            return view('tenant.dashboards.sports-facilitator');
        }

        if ($user?->hasTenantRole('team_coach')) {
            return view('tenant.dashboards.team-coach');
        }

        if ($user?->hasTenantRole('student_player')) {
            return view('tenant.dashboards.student-player');
        }

        abort(403, 'Unknown tenant role.');
    }
}
