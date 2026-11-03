<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterAuditLogsRequest;
use App\Models\{AuditLog, User};
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function index(FilterAuditLogsRequest $request)
    {
        Gate::authorize('view-audit-logs');

        $filters = $request->validated();

        $logs = AuditLog::with('user.employee')
            ->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['entity_type'] ?? null, fn ($q, $type) => $q->where('entity_type', $type))
            ->when($filters['entity_id'] ?? null, fn ($q, $entityId) => $q->where('entity_id', $entityId))
            ->when($filters['action'] ?? null, fn ($q, $action) => $q->where('action', 'like', '%' . $action . '%'))
            ->when($filters['date_from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['date_to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $users = User::with('employee')->orderBy('email')->get();
        $entityTypes = AuditLog::select('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type')
            ->all();

        return view('admin.audit.index', compact('logs', 'filters', 'users', 'entityTypes'));
    }

    public function show(AuditLog $auditLog)
    {
        Gate::authorize('view-audit-logs');

        $auditLog->load('user.employee');

        return view('admin.audit.show', compact('auditLog'));
    }
}
