<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateTenantRbacMatrixRequest;
use App\Support\TenantPermissionMatrix;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantRbacController extends Controller
{
    public function index(TenantPermissionMatrix $permissionMatrix): View
    {
        return view('tenant.rbac.index', [
            'definitions' => $permissionMatrix->definitions(),
            'moduleSummaries' => $permissionMatrix->moduleSummaries(),
            'managedRoles' => $permissionMatrix->managedRoles(),
            'matrix' => $permissionMatrix->matrix(),
        ]);
    }

    public function update(UpdateTenantRbacMatrixRequest $request, TenantPermissionMatrix $permissionMatrix): RedirectResponse
    {
        /** @var array<string, array<string, bool>> $submittedMatrix */
        $submittedMatrix = $request->validated('permissions');

        $permissionMatrix->persist($submittedMatrix, auth()->id());

        return redirect()
            ->route('tenant.rbac.index')
            ->with('status', 'RBAC matrix updated successfully.');
    }
}
