<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FilterCatalogRequest, RejectApprovalRequest, SavePositionRequest, UpdateCatalogStatusRequest};
use App\Models\{ChangeRequest, Position};
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function index(FilterCatalogRequest $request)
    {
        $this->authorize('viewAny', Position::class);

        $viewer = $request->user();
        $filters = $request->validated();

        $positions = Position::withCount([
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

        return view('admin.position.index', compact('positions', 'filters'));
    }

    public function create()
    {
        $this->authorize('create', Position::class);

        return view('admin.position.create');
    }

    public function store(SavePositionRequest $request)
    {
        $this->authorize('create', Position::class);

        $actor = $request->user();
        $data = $request->validated();

        $position = DB::transaction(function () use ($actor, $data) {
            if ($actor->needsApproval()) {
                $data['approval_status'] = 'pending_approval';
                $data['approved_by'] = null;
                $data['approved_at'] = null;
            } else {
                $data['approval_status'] = 'approved';
                $data['approved_by'] = $actor->id;
                $data['approved_at'] = now();
            }

            $model = Position::create($data);
            AuditLogger::log('create', 'position', $model->id, null, $model->toArray());

            return $model;
        });

        $message = $actor->needsApproval()
            ? 'Đã tạo chức vụ và gửi chờ Giám đốc phê duyệt.'
            : 'Đã tạo chức vụ thành công.';

        return redirect()->route('positions.show', $position)->with('success', $message);
    }

    public function show(Request $request, Position $position)
    {
        $this->authorize('view', $position);

        $position->loadCount([
            'employees' => fn ($q) => $q->visibleTo($request->user()),
        ]);

        return view('admin.position.show', compact('position'));
    }

    public function edit(Position $position)
    {
        $this->authorize('update', $position);

        return view('admin.position.edit', compact('position'));
    }

    public function update(SavePositionRequest $request, Position $position)
    {
        $this->authorize('update', $position);

        $data = $request->validated();
        $appliedDirectly = $this->applyOrRequestUpdate($request->user(), $position, $data);

        $message = $appliedDirectly
            ? 'Đã cập nhật thông tin chức vụ.'
            : 'Đề xuất cập nhật chức vụ đã được gửi chờ Giám đốc duyệt.';

        return redirect()->route('positions.show', $position)->with('success', $message);
    }

    public function updateStatus(UpdateCatalogStatusRequest $request, Position $position)
    {
        $this->authorize('update', $position);

        $data = $request->validated();
        $appliedDirectly = $this->applyOrRequestUpdate($request->user(), $position, $data);

        $message = $appliedDirectly
            ? 'Đã cập nhật trạng thái chức vụ.'
            : 'Đề xuất đổi trạng thái chức vụ đã được gửi chờ Giám đốc duyệt.';

        return back()->with('success', $message);
    }

    public function destroy(Request $request, Position $position)
    {
        $actor = $request->user();
        abort_unless($actor->hasManagementAccess(), 403);

        if ($position->employees()->count() > 0) {
            return back()->withErrors([
                'position' => 'Không thể xóa phòng ban/chức vụ đang còn nhân viên!',
            ]);
        }

        $this->authorize('delete', $position);

        if ($actor->needsApproval()) {
            DB::transaction(function () use ($actor, $position) {
                $oldData = $position->only(['id', 'name', 'description', 'status']);
                ChangeRequest::create([
                    'entity_type' => 'position',
                    'entity_id' => $position->id,
                    'action' => 'delete',
                    'old_data' => $oldData,
                    'new_data' => null,
                    'status' => 'pending',
                    'requested_by' => $actor->id,
                    'requested_at' => now(),
                ]);

                AuditLogger::log('request_delete', 'position', $position->id, $oldData, null);
            });

            return redirect()
                ->route('positions.index')
                ->with('success', 'Đã gửi đề xuất xóa chức vụ chờ Giám đốc phê duyệt.');
        }

        DB::transaction(function () use ($position) {
            $oldData = $position->only(['id', 'name', 'description', 'status']);
            $position->delete();
            AuditLogger::log('delete', 'position', $position->id, $oldData, null);
        });

        return redirect()->route('positions.index')->with('success', 'Đã xóa mềm chức vụ thành công.');
    }

    public function approve(Request $request, Position $position)
    {
        $actor = $request->user();
        abort_unless($actor->isDirector(), 403, 'Chỉ Giám đốc mới có quyền phê duyệt.');

        DB::transaction(function () use ($actor, $position) {
            $position = Position::lockForUpdate()->findOrFail($position->id);
            $old = ['approval_status' => $position->approval_status];

            $position->update([
                'approval_status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            AuditLogger::log('approve', 'position', $position->id, $old, ['approval_status' => 'approved']);
        });

        return back()->with('success', 'Đã phê duyệt chức vụ.');
    }

    public function reject(RejectApprovalRequest $request, Position $position)
    {
        $actor = $request->user();
        abort_unless($actor->isDirector(), 403, 'Chỉ Giám đốc mới có quyền từ chối.');
        $data = $request->validated();

        DB::transaction(function () use ($actor, $position, $data) {
            $position = Position::lockForUpdate()->findOrFail($position->id);
            $old = ['approval_status' => $position->approval_status];

            $position->update([
                'approval_status' => 'rejected',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'rejection_reason' => $data['rejection_reason'],
            ]);

            AuditLogger::log('reject', 'position', $position->id, $old, [
                'approval_status' => 'rejected',
                'rejection_reason' => $data['rejection_reason'],
            ]);
        });

        return back()->with('success', 'Đã từ chối chức vụ.');
    }

    private function applyOrRequestUpdate($actor, Position $model, array $data): bool
    {
        if ($actor->needsApproval()) {
            DB::transaction(function () use ($actor, $model, $data) {
                $oldData = $model->only(array_keys($data));
                ChangeRequest::create([
                    'entity_type' => 'position',
                    'entity_id' => $model->id,
                    'action' => 'update',
                    'old_data' => $oldData,
                    'new_data' => $data,
                    'status' => 'pending',
                    'requested_by' => $actor->id,
                    'requested_at' => now(),
                ]);

                AuditLogger::log('request_update', 'position', $model->id, $oldData, $data);
            });

            return false;
        }

        DB::transaction(function () use ($model, $data) {
            $model = Position::lockForUpdate()->findOrFail($model->id);
            $old = $model->only(array_keys($data));
            $model->fill($data);
            if ($model->isDirty()) {
                $model->save();
                AuditLogger::log('update', 'position', $model->id, $old, $data);
            }
        });

        return true;
    }
}
