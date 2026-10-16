<?php
namespace App\Http\Controllers;

use App\Models\{Contract, Employee, PayrollPeriod};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin()
    {
        return view('admin.welcome', [
            'employeeCount' => Employee::count(),
            'workingCount' => Employee::where('employment_status', 'working')->count(),
            'pendingContracts' => Contract::where('status', 'pending_approval')->count(),
            'pendingPayrolls' => PayrollPeriod::where('status', 'pending_approval')->count(),
        ]);
    }
    public function profile(Request $request)
    {
        $user = $request->user()->load(['role', 'employee.department', 'employee.position']);
        return view('user.profile.index', ['user' => $user, 'employee' => $user->employee]);
    }
    public function contracts(Request $request)
    {
        // Schema has no publication field: approved contracts are visible to their own employee.
        $contracts = $request->user()->employee->contracts()->where('status', 'approved')->latest('effective_from')->paginate(15);
        return view('user.contract.index', compact('contracts'));
    }
}
