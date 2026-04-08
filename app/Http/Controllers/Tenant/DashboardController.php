<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Support\TenantPermissionMatrix;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $permissionMatrix = app(TenantPermissionMatrix::class);

        if ($user?->hasTenantRole('university_admin')) {
            return view('tenant.dashboards.university-admin');
        }

        if ($user?->hasTenantRole('sports_facilitator')) {
            abort_unless($permissionMatrix->allows($user, 'facilitator.dashboard.view'), Response::HTTP_FORBIDDEN);

            return view('tenant.dashboards.sports-facilitator');
        }

        if ($user?->hasTenantRole('team_coach')) {
            abort_unless($permissionMatrix->allows($user, 'coach.dashboard.view'), Response::HTTP_FORBIDDEN);

            return view('tenant.dashboards.team-coach');
        }

        if ($user?->hasTenantRole('student_player')) {
            abort_unless($permissionMatrix->allows($user, 'player.dashboard.view'), Response::HTTP_FORBIDDEN);

            return view('tenant.dashboards.student-player');
        }

        abort(403, 'Unknown tenant role.');
    }
}
