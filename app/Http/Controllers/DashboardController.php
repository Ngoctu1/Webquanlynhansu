<?php

namespace App\Http\Controllers;

use App\Models\{Contract, Department, Employee};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin(Request $request)
    {
        $viewer = $request->user();

        return view('admin.welcome', [
            'employeeCount' => Employee::visibleTo($viewer)->count(),
            'workingCount' => Employee::visibleTo($viewer)->where('employment_status', 'working')->count(),
            'pendingContracts' => Contract::where('approval_status', 'pending_approval')
                ->whereHas('employee', fn ($q) => $q->visibleTo($viewer))
                ->count(),
            'departmentCount' => Department::count(),
            'recentEmployees' => Employee::visibleTo($viewer)
                ->with(['department', 'position'])
                ->latest()
                ->take(6)
                ->get(),
            'recentContracts' => Contract::with('employee')
                ->where('approval_status', 'pending_approval')
                ->whereHas('employee', fn ($q) => $q->visibleTo($viewer))
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function profile(Request $request)
    {
        return app(EmployeePortalController::class)->profile($request);
    }

    public function contracts(Request $request)
    {
        return app(EmployeePortalController::class)->contracts($request);
    }
}
