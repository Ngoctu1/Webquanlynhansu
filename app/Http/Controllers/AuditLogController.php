<?php
namespace App\Http\Controllers;

use App\Http\Requests\FilterAuditLogsRequest;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(FilterAuditLogsRequest $request)
    {
        $data = $request->validated();
        $logs = AuditLog::with('user.employee')
            ->when($data['action'] ?? null, fn ($q, $action) => $q->where('action', $action))
            ->when($data['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->latest('id')->paginate(25)->withQueryString();
        return view('admin.audit.index', compact('logs'));
    }
}
