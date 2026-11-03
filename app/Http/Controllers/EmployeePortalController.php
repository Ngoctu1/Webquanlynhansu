<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeePortalController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user()->load([
            'role',
            'employee.department',
            'employee.position',
            'employee.employeeDocuments',
            'employee.activeContract',
        ]);

        return view('user.profile.index', [
            'user' => $user,
            'employee' => $user->employee,
            'documents' => $user->employee?->employeeDocuments ?? collect(),
        ]);
    }

    public function contracts(Request $request)
    {
        $employee = $request->user()->employee;

        $contracts = $employee
            ? $employee->contracts()
                ->where('approval_status', 'approved')
                ->latest('effective_from')
                ->paginate(15)
            : collect();

        return view('user.contract.index', compact('contracts'));
    }
}
