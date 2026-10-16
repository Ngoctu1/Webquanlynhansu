<?php
namespace App\Http\Controllers;

use App\Models\PayrollDetail;
use Illuminate\Http\Request;

class PayrollDetailController extends Controller
{
    public function index(Request $request)
    {
        $details = PayrollDetail::with(['period', 'contract'])->where('employee_id', $request->user()->employee_id)
            ->whereHas('period', fn ($q) => $q->where('status', 'published'))->latest('id')->paginate(15);
        return view('user.payroll.index', compact('details'));
    }
}
