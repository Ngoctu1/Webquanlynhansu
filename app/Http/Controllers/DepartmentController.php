<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FilterCatalogRequest, RejectApprovalRequest, SaveDepartmentRequest, UpdateCatalogStatusRequest};
use App\Models\{ChangeRequest, Department};
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index(FilterCatalogRequest $request)
    {
        $this->authorize('viewAny', Department::class);

        $viewer = $request->user();
        $filters = $request->validated();

        $departments = Department::withCount([
            'employees' => fn ($q) => $q->visibleTo($viewer),
        ])
            ->when(isset($filters['q']), fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', '%' . $filters['q'] . '%')
                ->orWhere('description', 'like', '%' . $filters['q'] . '%')
            ))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['approval_status'] ?? null, fn ($q, $appStatus) => $q->where('approval_status', $appStatus))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.department.index', compact('departments', 'filters'));
    }

    public function create()
    {
        $this->authorize('create', Department::class);

        return view('admin.department.create');
    }

    public function store(SaveDepartmentRequest $request)
    {
        $this->authorize('create', Department::class);

        $actor = $request->user();
        $data = $request->validated();

        $department = DB::transaction(function () use ($actor, $data) {
            if ($actor->needsApproval()) {
                $data['approval_status'] = 'pending_approval';
                $data['approved_by'] = null;
                $data['approved_at'] = null;
            } else {
                $data['approval_status'] = 'approved';
                $data['approved_by'] = $actor->id;
                $data['approved_at'] = now();
            }

            $model = Department::create($data);
            AuditLogger::log('create', 'department', $model->id, null, $model->toArray());

            return $model;
        });

        $message = $actor->needsApproval()
            ? 'Đã tạo phòng ban và gửi chờ Giám đốc phê duyệt.'
            : 'Đã tạo phòng ban thành công.';

        return redirect()->route('departments.show', $department)->with('success', $message);
    }

    public function show(Request $request, Department $department)
    {
        $this->authorize('view', $department);

        $department->loadCount([
            'employees' => fn ($q) => $q->visibleTo($request->user()),
        ]);

        return view('admin.department.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $this->authorize('update', $department);

        return view('admin.department.edit', compact('department'));
    }

    public function update(SaveDepartmentRequest $request, Department $department)
    {
        $this->authorize('update', $department);

        $data = $request->validated();
        $appliedDirectly = $this->applyOrRequestUpdate($request->user(), $department, $data);

        $message = $appliedDirectly
            ? 'Đã cập nhật thông tin phòng ban.'
            : 'Đề xuất cập nhật phòng ban đã được gửi chờ Giám đốc duyệt.';

        return redirect()->route('departments.show', $department)->with('success', $message);
    }

    public function updateStatus(UpdateCatalogStatusRequest $request, Department $department)
    {
        $this->authorize('update', $department);

        $data = $request->validated();
        $appliedDirectly = $this->applyOrRequestUpdate($request->user(), $department, $data);

        $message = $appliedDirectly
            ? 'Đã cập nhật trạng thái phòng ban.'
            : 'Đề xuất đổi trạng thái phòng ban đã được gửi chờ Giám đốc duyệt.';

        return back()->with('success', $message);
    }

    public function destroy(Request $request, Department $department)
    {
        $actor = $request->user();
        abort_unless($actor->hasManagementAccess(), 403);

        if ($department->employees()->count() > 0) {
            return back()->withErrors([
                'department' => 'Không thể xóa phòng ban/chức vụ đang còn nhân viên!',
            ]);
        }

        $this->authorize('delete', $department);

        if ($actor->needsApproval()) {
            DB::transaction(function () use ($actor, $department) {
                $oldData = $department->only(['id', 'name', 'description', 'status']);
                ChangeRequest::create([
                    'entity_type' => 'department',
                    'entity_id' => $department->id,
                    'action' => 'delete',
                    'old_data' => $oldData,
                    'new_data' => null,
                    'status' => 'pending',
                    'requested_by' => $actor->id,
                    'requested_at' => now(),
                ]);

                AuditLogger::log('request_delete', 'department', $department->id, $oldData, null);
            });

            return redirect()
                ->route('departments.index')
                ->with('success', 'Đã gửi đề xuất xóa phòng ban chờ Giám đốc phê duyệt.');
        }

        DB::transaction(function () use ($department) {
            $oldData = $department->only(['id', 'name', 'description', 'status']);
            $department->delete();
            AuditLogger::log('delete', 'department', $department->id, $oldData, null);
        });

        return redirect()->route('departments.index')->with('success', 'Đã xóa mềm phòng ban thành công.');
    }

    public function approve(Request $request, Department $department)
    {
        $actor = $request->user();
        abort_unless($actor->isDirector(), 403, 'Chỉ Giám đốc mới có quyền phê duyệt.');

        DB::transaction(function () use ($actor, $department) {
            $department = Department::lockForUpdate()->findOrFail($department->id);
            $old = ['approval_status' => $department->approval_status];

            $department->update([
                'approval_status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            AuditLogger::log('approve', 'department', $department->id, $old, ['approval_status' => 'approved']);
        });

        return back()->with('success', 'Đã phê duyệt phòng ban.');
    }

    public function reject(RejectApprovalRequest $request, Department $department)
    {
        $actor = $request->user();
        abort_unless($actor->isDirector(), 403, 'Chỉ Giám đốc mới có quyền từ chối.');
        $data = $request->validated();

        DB::transaction(function () use ($actor, $department, $data) {
            $department = Department::lockForUpdate()->findOrFail($department->id);
            $old = ['approval_status' => $department->approval_status];

            $department->update([
                'approval_status' => 'rejected',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'rejection_reason' => $data['rejection_reason'],
            ]);

            AuditLogger::log('reject', 'department', $department->id, $old, [
                'approval_status' => 'rejected',
                'rejection_reason' => $data['rejection_reason'],
            ]);
        });

        return back()->with('success', 'Đã từ chối phòng ban.');
    }

    private function applyOrRequestUpdate($actor, Department $model, array $data): bool
    {
        if ($actor->needsApproval()) {
            DB::transaction(function () use ($actor, $model, $data) {
                $oldData = $model->only(array_keys($data));
                ChangeRequest::create([
                    'entity_type' => 'department',
                    'entity_id' => $model->id,
                    'action' => 'update',
                    'old_data' => $oldData,
                    'new_data' => $data,
                    'status' => 'pending',
                    'requested_by' => $actor->id,
                    'requested_at' => now(),
                ]);

                AuditLogger::log('request_update', 'department', $model->id, $oldData, $data);
            });

            return false;
        }

        DB::transaction(function () use ($model, $data) {
            $model = Department::lockForUpdate()->findOrFail($model->id);
            $old = $model->only(array_keys($data));
            $model->fill($data);
            if ($model->isDirty()) {
                $model->save();
                AuditLogger::log('update', 'department', $model->id, $old, $data);
            }
        });

        return true;
    }
}
